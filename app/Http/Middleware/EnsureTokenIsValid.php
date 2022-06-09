<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use QueueIT\KnownUserV3\SDK\KnownUser;
use Illuminate\Support\Facades\Storage;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $fullUrl = URL::full();
        $currentUrlWithoutQueueitToken = preg_replace("/([\\?&])("."queueittoken"."=[^&]*)/i", "", $fullUrl);

        $configText = Storage::disk('local')->get('integrationconfig.json');
        $jsonDecoded = json_decode($configText, false);
        $customerID = $jsonDecoded->CustomerId; //Your Queue-it customer ID
        $secretKey = config('app.qit_secret_key'); //Your 72 char secret key as specified in Go Queue-it self-service platform

        $queueittoken = isset($_GET["queueittoken"])? $_GET["queueittoken"] :'';
      
        //Verify if the user has been through the queue
        $result = KnownUser::validateRequestByIntegrationConfig(
            $currentUrlWithoutQueueitToken,
            $queueittoken,
            $configText,
            $customerID,
            $secretKey
        );

        try {
            $currentUrlWithoutQueueitToken = preg_replace("/([\\?&])("."queueittoken"."=[^&]*)/i", "", $fullUrl);
                
            if ($result->doRedirect()) {
                //Adding no cache headers to prevent browsers to cache requests
                header("Expires:Fri, 01 Jan 1990 00:00:00 GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");
                //end
            
                if (!$result->isAjaxResult) {
                    //Send the user to the queue - either because hash was missing or because is was invalid
                    header('Location: ' . $result->redirectUrl);
                } else {
                    header('HTTP/1.0: 200');
                    header($result->getAjaxQueueRedirectHeaderKey() . ': '. $result->getAjaxRedirectUrl());
                }
                
                die();
            }
            if (!empty($queueittoken) && $result->actionType == "Queue") {
                //Request can continue - we remove queueittoken form querystring parameter to avoid sharing of user specific token
                header('Location: ' . $currentUrlWithoutQueueitToken);
                die();
            }
        } catch (\Exception $e) {
            // There was an error validating the request
            // Use your own logging framework to log the error
            // This was a configuration error, so we let the user continue
        }

        return $next($request);
    }
}
