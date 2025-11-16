<?php
namespace App\External;

use Tk\Config;
use Tk\Exception;
use Tk\Log;
use Tk\Uri;

/**
 *
 * @deprecated Testing example, remove for release
 */
class Moodle
{

    protected string $apiUrl = '';
    protected string $token  = '';


    public function __construct(string $apiUrl, string $token)
    {
        if (empty($apiUrl) || empty($token)) {
            throw new Exception("invalid API URL or token");
        }
        $this->apiUrl = $apiUrl;
        $this->token  = $token;
    }

    public static function create(?string $apiUrl = null, ?string $token = null): Moodle
    {
        if (is_null($apiUrl)) {
            $apiUrl = Config::getValue('moodle.api.url', '');
        }
        if (is_null($token)) {
            $token = Config::getValue('moodle.api.token', '');
        }
        return new self($apiUrl, $token);
    }

	/**
	 * low-level generic Moodle API request function, GET and POST
	 * returns object, array, or null depending on Moodle return
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
		$params['wstoken']            = $this->token;
		$params['moodlewsrestformat'] = 'json';
		$params['wsfunction']         = $endpoint;

        $url = Uri::create($this->apiUrl . '/webservice/rest/server.php', $params);
		$ch = curl_init($url->toString());
        if (!($ch instanceof \CurlHandle)) {
            throw new Exception('Failed to init CURL for moodle api request');
        }

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT        => 180,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 10,
		]);

        if (Config::isDev()) {
            // @phpstan-ignore-next-line
            curl_setopt_array($ch, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
        }

		if ($httpMethod == "POST") {
			curl_setopt_array($ch, [
				CURLOPT_POST       => true,
				CURLOPT_POSTFIELDS => http_build_query($wsParams),
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

		// check for exception returned from Moodle
		if (is_array($result) && ($result['exception'] ?? '')) {
            Log::error(sprintf("Moodle error (%s): %s", $result["errorcode"] ?? 0, $result["message"] ?? "unknown"));
            return false;
		} elseif (is_object($result) && ($result->exception ?? '')) {
            Log::error(sprintf("Moodle error (%s): %s", $result->errorcode ?? 0, $result->message ?? "unknown"));
            return false;
		}

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


	public function getMoodleSiteInfo(): ?\stdClass
	{
		return $this->post('core_webservice_get_site_info');
	}

    /**
     * @return array<int,\stdClass>
     */
	public function getCourseCategories(): array
	{
		return $this->post('core_course_get_categories');
	}

	public function getCategoryIdByIdnumber(string $idnumber): ?int
	{
        $params = [
            'criteria' => [
                [
                    'key' => 'idnumber',
                    'value' => $idnumber
                ]
            ],
            //'addsubcategories' => 0
        ];
        $rows = $this->get('core_course_get_categories', $params);
		if (!is_array($rows)) return null;
		vd($params, $rows);

		return is_null($rows[0]->id ?? null) ? null : intval($rows[0]->id);
	}

    // -----------------------------------------------


	public function setCategoryVisible(int $id, bool $visible): bool
	{
		$this->post('local_sisapi_set_category_visible', compact('id', 'visible'));
        return true;
	}

	public function get_all_recent_quizzes(int $lookback, array $clinical_ids = []): array
	{
		return $this->post('local_oumquizzes_get_recent', compact('lookback', 'clinical_ids'));
	}

	public function get_curriculum_managers(int $category_id): array
	{
		return $this->post('local_oumuser_get_curriculum_managers', compact('category_id')) ?: [];
	}

	public function get_all_examsoft_eor(array $course_ids = []): array
	{
		return $this->post('local_oumgrades_get_examsoft_eor', compact('course_ids'));
	}

    /**
     * set moodle course dates from unit dates (in organization timezone)
     * add 2 weeks to end date per OUM practice to allow for extensions, review
     * update fullname from start date
     */
	public function set_course_dates(int $course_id, string $start_on, ?string $end_on = null): bool
	{
        if (!is_null($end_on)) {
            //$end_on = (new \DateTime($end_on, new \DateTimeZone(ORGANIZATION_TIMEZONE)))->add(new \DateInterval('P2W'))->getTimestamp();
            $end_on = (new \DateTime($end_on))->getTimestamp();
        }

		$ok = $this->post('local_oumcourse_set_dates', [
            'id' => $course_id,
            //'startdate' => (new \DateTime($start_on, new \DateTimeZone(ORGANIZATION_TIMEZONE)))->getTimestamp(),    // SIS
            'startdate' => (new \DateTime($start_on))->getTimestamp(),
            'enddate' => is_null($end_on) ? null : $end_on,
        ]);

        return ($ok !== false);
	}


    public function getSchedule(int $courseId): array
    {
        $result = $this->post('sishurdle_get_schedule', [
            'courseid' => $courseId
        ]);
        if (!is_object($result)) return [];

        $students = $result->students ?? [];
        $students = array_column($students, null, 'userid');
        $rows = [];

        foreach ($students as $student) {

        }

        return $students;
    }





    /**
     * returns array of user object [user, user, ...]
     * user {
     *   id
     *   username
     *   firstname
     *   lastname
     *   fullname
     *   email
     *   (address, phone, various contact information)
     *   idnumber
     *   firstaccess
     *   lastaccess
     *   (auth, suspended, lang, etc.)
     *   timezone
     *   profileimageurl
     *   profileimageurlsmall
     *
     */
    public function getAllUsers(): array
    {
        $result = $this->post('local_oumuser_get_users', [
            'criteria' => [
                [
                    'key' => 'firstname',
                    'value' => '%'
                ]
            ]
        ]);

        return is_object($result) ? ($result->users ?? []) : [];
    }

//    public function getCohorts(string $idnumber = ''): array
//    {
//        $params = array(
//            'query' => $idnumber,
//            'context' => [
//                'contextid' => 10,
//                'contextlevel' => 'system',
//                'instanceid' => 0,
//            ],
//            //'limitnum' => 100,
//        );
//        // I think the error here is caused by not having a valid SSL cert locally
//        $result = $this->get('core_cohort_search_cohorts', $params);
//        //$result = $this->get('tool_lp_search_cohorts', $params);
//
//        return is_object($result) ? $result : [];
//    }

	public function getAllCohorts(): array
	{
		$result = $this->post('core_cohort_get_cohorts', []);
		return is_array($result) ? $result : [];
	}


    /**
     * get all available role information from moodle
     *
     * @return array<string, \stdClass>
     */
	public function getRoles(): array
	{
		$rows = $this->post('local_sisrole_get_roles');
        if (!is_array($rows)) return [];
		return array_column($rows, null, 'shortname');
	}

    /**
     * get role information from moodle using its shortname
     */
	public function getRole(string $shortname): ?\stdClass
	{
		$rows = $this->getRoles();
		return $rows[$shortname] ?? null;
	}

    public function getForums(): array
    {

        $result = $this->post('mod_forum_get_forums_by_courses', [
            'courseids'   => [1]
        ]);

        return $result;
    }

//
//    // get a list of competencies for a course
//	public function get_course_competencies(int $courseid): array
//	{
//		$courseid = 3;
//
//		$rows = $this->post('core_competency_list_course_competencies', ['id' => $courseid]);
//
//		$competencies = [];
//		foreach ($rows as $row) {
//			$competencies[] = (object)[
//				'id' => $row->competency->id,
//				'shortname' => trim($row->competency->shortname),
//				'idnumber' => $row->competency->idnumber,
//				'description' => $row->competency->description,
//				'sortorder' => $row->competency->sortorder,
//				'parentid' => $row->competency->parentid,
//			];
//		}
//
//		return $competencies;
//	}
//
//
//    // Get a student's course completion progress in a course (does not relate to competencies)
//    public function get_course_completion_status(): mixed
//    {
//        $courseid = 4;
//        $userid = 99;
//
//        //$rows = $this->post('core_completion_get_activities_completion_status', ['courseid' => $courseid, 'userid' => $userid]);
//        $rows = $this->post('core_completion_get_course_completion_status', [
//            'courseid' => $courseid,
//            'userid' => $userid
//        ]);
//
//        return $rows;
//    }
//
//    public function read_user_evidence(): mixed
//    {
//        $courseid = 4;
//        $userid = 81;
//
//        $rows = $this->post('core_competency_read_user_evidence', [
//            //'courseid' => $courseid,
//            'id' => $userid
//        ]);
//
//        return $rows;
//    }
//
//



    /**
     * @return array<string, \stdClass>
     */
    public function get_competency_frameworks(): array
    {
        $rows = $this->post('core_competency_list_competency_frameworks', [
            'context' => [
                'contextid' => 0,
                'contextlevel' => 'system',
                'instanceid' => 0,
            ]
        ]);
        return array_column($rows, null, 'idnumber');
    }

    /**
     * return a frameworks competency list
     */
    public function get_competency_list(string $idnumber): \stdClass
    {
        return $this->post('local_siscompetency_get_competency_list', compact('idnumber'));
    }

    /**
     * return competency progress for a single student
     */
    public function get_user_competencies(int $userid, string $framework_idnumber): \stdClass
    {
        $result = $this->post('local_siscompetency_get_user_competencies',
            [
                'userid' => $userid,
                'framework_idnumber' => $framework_idnumber,
            ]
        );
		if (!($result instanceof \stdClass)) return new \stdClass;
        return $result;
    }

    /**
     * return competency progress for a single student
     *
     * @return array<int,\stdClass>
     */
    public function get_enrolled_competencies(string $course_shortname, string $framework_idnumber): array
    {
        $rows = $this->post('local_siscompetency_get_enrolled_competencies',
            [
                'course_shortname' => $course_shortname,
                'framework_idnumber' => $framework_idnumber,
            ]
        );
		if (!is_array($rows)) return [];
        return array_column($rows, null, 'userid');
    }

    /**
     * return competency progress for a single student
     */
    public function get_recent_quizzes_assignments(string $lookback, array $course_ids = []): mixed
    {
        $rows = $this->post('local_sisquizzes_get_recent_quizzes_assignments',
            compact('lookback', 'course_ids')
        );
        return $rows;
    }

    /**
     * return competency progress for a single student
     */
    public function get_examsoft_eor(array $course_ids = []): mixed
    {
        $rows = $this->post('local_sisgrades_get_examsoft_eor',
            compact('course_ids')
        );
        return $rows;
    }

}