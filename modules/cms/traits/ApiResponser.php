<?php
namespace Cms\Traits;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use October\Rain\Support\Facades\File;
use RainLab\Translate\Models\Message;

trait ApiResponser
{
    public $Langs = [];
    public static $SUCCESS = 'success';
    public static $FAIL = 'fail';
    public static $WRONG_CODE = 'wrong_code';
    public static $ERROR_CODES = [
        'UNKNOWN'               => 0,
        'VALIDATION_ERROR'      => 408, //ბექენდის მხარეს ვალიდაცია ვერ გაიარა
        'AUTH_ERROR'            => 499  , //ავტორიზაციის პრობლემა
        'REFRESH_TOKEN_ERROR'   => 409, //ტოკენის რეფრეში
        'NOT_FOUND'             => 404, //ვერ იპოვა
        'VERIFICATION_ERROR'    => 410, //ვერიფიკაცია ვერ გაიარა (ტელეფონის, მეილის და ა.შ.)
        'FORBIDDEN'             => 411, //აკრძალულია (ნებისმიერი აკრძალვა ბექის მხარეს)
        'PAYMENT_ERROR'         => 412, //გადახდის შეცდომები
        'PASSWORD_RESET_ERROR'  => 413, //პაროლის აღდგენის შეცდომები
        'NEEDS_VERIFICATION'    => 414, //საჭიროებს ვერიფიკაციას
        'USER_DELETED'          => 415
    ];


    /**
     * @var null
     */
    public $responseStatusCode = null;
    /**
     * @var string
     */
    public $responseStatusMessage = '';

//    public function __construct()
//    {
//        $this->getLangs();
//    }


    /**
     * Build Success Response;
     * @param $data
     * @param int $code
     * @param string $statusMessage
//     * @return \Illuminate\Http\JsonResponse
     */

    public function response($data = [], $code = null, $statusMessage = 'Success')
    {
        if (!$code && empty($data)) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }
        $code = $code ?: (!empty($data) ? Response::HTTP_OK : false);
        $data = (array)$data;

        if ($code == 1 or $code == Response::HTTP_OK) {
            return $this->successResponse($data, $code, $statusMessage);
        }
        return $this->errorResponse($statusMessage, $code, $data);
    }

    public function successResponse($data = [], $code = Response::HTTP_OK, $statusMessage = null) {
        $response["statusCode"] = $code == 1 ? Response::HTTP_OK : $code;
        $response["statusMessage"] = $this->translateStatusMessage($statusMessage ?: self::$SUCCESS);
        $response['data'] = $data;
        $response['versioning'] = $this->getVersioning();
        return \response()->json($response, Response::HTTP_OK);
    }

    /**
     * Build Error Response
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message = 'Failed', int $code = 0, $data = [])
    {
        $response["statusCode"] = $code ?: self::$ERROR_CODES['UNKNOWN'];
        $Message = $message ?: self::$FAIL;
        $response["statusMessage"] = $code ? $this->translateStatusMessage($Message) : $Message;
        $response['data'] = $data;
        $response['versioning'] = $this->getVersioning();

        return \response()->json($response, Response::HTTP_OK);
    }

    public function translate($message) {
        return $this->translateStatusMessage($message);
    }

    private function translateStatusMessage($message)
    {
        if (is_object($message)) {
            $message = $message->toArray();
        }
        if (is_array($message)) {
            return $this->translateArrayItem($message);
        }
        return $this->getOrInsertTranslation($message);

    }

    private function translateArrayItem($Array)
    {
        $Return = '';
        array_walk_recursive(
            $Array,
            function (&$value) use (&$Return) {
                if (!is_array($value)) {
                    $Return .=  $this->getOrInsertTranslation($value)." \n ";
                }
            }
        );

        return $Return;
    }

    private function getOrInsertTranslation($message)
    {
        if (strlen($message) > 100) {
            return $message;
        }
        $Langs = $this->getLangs();
        $translation = Arr::get($Langs, $message);
        if (!$translation) {
            if (!Message::where('code', $message)->first()) {
                (new Message)->create([
                    'code' => $message,
                    'message_data' => ['x' => $message]
                ]);
            }
            return $message;
        }
        return $translation;
    }

    private function getLangs()
    {
        if (!empty($this->Langs)) {
            return $this->Langs;
        }
        $lang = $this->detectLang();
        $lang = $this->checkLang($lang);
        $langPath = storage_path().'/app/langs/%s.json';
        $Langs = file_get_contents(sprintf($langPath, $lang));
        if (!$Langs) {
            $this->Langs = [];
            return;
        }
        $Langs = json_decode($Langs, 1);
        $this->Langs = $Langs;
        return $Langs;
    }

    public function detectLang()
    {
        $urlPaths = explode('/', request()->getRequestUri());
        return Arr::get($urlPaths, 1);
    }

    public function checkLang($lang)
    {
        $langPath = storage_path().'/app/langs/%s.json';
        if (!file_exists(sprintf($langPath, $lang))) {
            return config('app.default_lang');
        }
        return $lang;
    }

    private function getVersioning()
    {
        $langsVersion = json_decode(File::get(storage_path().'/app/versions.json', 0), 1);
        if (!is_array($langsVersion)) {
            $langsVersion = [];
        }
        return array_merge($langsVersion, config('app.versioning'));

    }
}