<?php
namespace App\External;

use Tk\Config;
use Tk\Exception;
use Tk\Log;
use Tk\Uri;

/**
 *
 */
class OpenAi
{

    protected string $apiUrl   = 'http://192.168.0.42:1234/v1';
    protected string $model    = 'nomic-ai/nomic-embed-text-v1.5-GGUF';


    public function __construct(string $apiUrl, string $model)
    {
        $this->apiUrl = $apiUrl;
        $this->model  = $model;
    }

    public static function create(?string $apiUrl = null, ?string $model = null): OpenAi
    {
        return new self($apiUrl, $model);
    }

	/**
	 * low-level generic API request function, GET and POST
	 * returns object, array, or null depending on return
	 * on error returns false and error description in self::$error
	 */
	protected function request(string $httpMethod, string $endpoint, ?array $wsParams = null): mixed
	{
		$httpMethod = strtoupper($httpMethod);
		assert($httpMethod == 'GET' || $httpMethod == 'POST', "unsupported HTTP method {$httpMethod}");

		if (!is_array($wsParams)) $wsParams = [];
		$params = [];
		if ($httpMethod == 'GET') {
			// if GET request send params in query string
			$params = $wsParams;
		}


        $url = Uri::create($this->apiUrl . $endpoint, $params);
		$ch = curl_init($url->toString());
        if (!($ch instanceof \CurlHandle)) {
            throw new Exception('Failed to init CURL for api request');
        }

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT        => 180,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_HTTPHEADER     => [
				"Content-Type: application/json",
			],
		]);

        if (Config::isDev()) {
            curl_setopt_array($ch, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
        }

		if ($httpMethod == "POST") {
			curl_setopt_array($ch, [
				CURLOPT_POST       => true,
				//CURLOPT_POSTFIELDS => http_build_query($wsParams),
				CURLOPT_POSTFIELDS => json_encode($wsParams),
			]);
		}

		$curlError = curl_error($ch);
		$curlErrno = curl_errno($ch);
		$jsonResponse = curl_exec($ch);
		$respcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

		curl_close($ch);
		if ($curlError) {
            Log::error("curl error {$curlErrno} '{$curlError}'");
			return false;
		}

		if ($respcode < 200 || $respcode >= 300) {
            Log::error("HTTP error {$respcode} from {$httpMethod} '{$url}'");
            Log::error(strval($jsonResponse));
			return false;
		}
		if (!is_string($jsonResponse)) return null;
		$result = json_decode($jsonResponse);

		if (is_object($result)) $result = (object) get_object_vars($result);

        // todo add some error handling

		return $result;
	}

	protected function get(string $endpoint, ?array $wsParams = null): mixed
	{
		return $this->request("GET", $endpoint, $wsParams);
	}

	protected function post(string $endpoint, ?array $wsParams = null): mixed
	{
		return $this->request("POST", $endpoint, $wsParams);
	}


	public function getModels(): array
	{
		return $this->get('/models')->data ?? [];
	}

	public function askQuestion(string $model, string $question, string $role = 'user'): mixed
	{

		$result = $this->post('/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => $role,
                    'content' => $question
                ]
            ],
            'temperature' => 0.7,
        ]);

        return $result->choices[0]->message->content ?? '{chat error}';
	}

}