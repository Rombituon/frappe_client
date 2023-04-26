<?php

namespace Rombituon\FrappeClient;
use Rombituon\FrappeClient\Exceptions\FrappeException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class FrappeClient 
{
    private $frappeUrlApi;
    private $token;
    private $frappeUrlResource;
    private $httpClient;
    private $_contents;
    private $_authHeader;
    public function __construct()
    {
        

        $this->token = config("frappe.api_token");
        $this->frappeUrlApi = config("frappe.url") . "/api/method";
        $this->frappeUrlResource = config("frappe.url") . "/api/resource";
        $this->httpClient = new Client();

        $this->_authHeader = [
            'Authorization'=>'token '.$this->token
        ];
    }

    public function getAuthResponse()
    {
        return $this->_contents;
    }

    private function _makeRequest($module_url, $options=[], $httpMethod='GET')
    {
        try
        {
            $response = $this->httpClient->request($httpMethod,
                $this->frappeUrlApi.$module_url,
                [
                    'headers'=> $this->_authHeader,
                    'query'=> $options
                ]
            );

            if($response->getStatusCode()==200){
                return json_decode($response->getBody()->getContents());
            }


        }
        catch(ClientException $e)
        {
            throw $e;
        }
    }


    public function get($module_url, $options=[])
    {
        return $this->_makeRequest($module_url, $options, 'GET');
    }

    public function post($module_url, $options=[])
    {
        return $this->_makeRequest($module_url, $options, 'POST');
    }

    private function _buildUriForFrappeResource($url, $options)
    {
        $query = [];
        if(isset($options['filters'])){
            $query['filters'] = json_encode($options['filters']);
        }
        if(isset($options['fields'])){
            $query['fields'] = json_encode($options['fields']);
        }
        if(isset($options['limit_start'])){
            $query['limit_start'] = json_encode($options['limit_start']);
        }
        if(isset($options['limit_page_length'])){
            $query['limit_page_length'] = json_encode($options['limit_page_length']);
        }
        
        if($query){
            $url .= '?';
            foreach($query as $key => $value){
                $url .= $key.'='.$value.'&';
            }
        }

        return $url;
    }


    public function resource($doctype, $options=[], $httpMethod='GET')
    {
        $url = $this->frappeUrlResource . '/' . $doctype;
        try 
        {

            $buildUrl = $this->_buildUriForFrappeResource($url,$options);   

            $response = $this->httpClient->request($httpMethod,
                            $buildUrl,
                            [
                                'headers'=>$this->_authHeader
                            ]
                        );

            return json_decode($response->getBody()->getContents());
        }
        catch(ClientException $e)
        {
            throw new FrappeException(
                $response->getReasonPhrase(), 
                $response->getStatusCode()
            );
        }
    }

    public function auth($throwable = false)
    {
        try {
            
                $response = $this->httpClient->request('POST',
                            $this->frappeUrlApi."/frappe.auth.get_logged_user",
                            [
                                'headers'=>$this->_authHeader
                            ]
                        );
        
       
                if($response->getStatusCode()==200){
                    $this->_contents = $response->getBody()->getContents();
                    return true;
                }elseif ($response->getStatusCode()==403){
                    $this->_contents = json_decode($response->getBody()->getContents());
                    return false;
                }
        
       

        }
        catch(ClientException $e){

            if($throwable){
                throw new FrappeException(
                     $response->getReasonPhrase(), 
                     $response->getStatusCode()
                 );
            } 

            return false;
        }
        
       
       
    }

    public function getHello(){
        return 'Hello World!';
    }

}