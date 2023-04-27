<?php

namespace Rombituon\FrappeClient;
use Rombituon\FrappeClient\Exceptions\FrappeException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
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
    private $_response;
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

    private function _makeRequest($module_url, $options=[], $httpMethod='GET', $headers = [])
    {
        try
        {
            $response = $this->httpClient->request($httpMethod,
                $this->frappeUrlApi.$module_url,
                [
                    'headers'=> array_push($this->_authHeader, $headers),
                    'query'=> $options
                ]
            );

            if($response->getStatusCode()==200){
                $data = json_decode($response->getBody()->getContents());
                return $data->data;
            }


        }
        catch(ClientException $e)
        {
            throw $e;
        }
    }


    public function doGet($module_url, $options=[], $headers=[])
    {
        return $this->_makeRequest($module_url, $options, 'GET',$headers);
    }

    public function doPost($module_url, $options=[], $headers=[])
    {
        return $this->_makeRequest($module_url, $options, 'POST', $headers);
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

    /**
     * Gets the data from the response body as an object
     *
     * @return object The data from the response body.
     */
    public function get()
    {
        $res = json_decode($this
                    ->_response
                    ->getBody()
                    ->getContents());
        return $res->data;
    }

    public function toJson()
    {
        $res = json_decode($this->
                _response
                ->getBody()
                ->getContents());

        return json_encode($res->data);
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

            $this->_response = $response;

            
        }
        catch(ServerException $e){

            $this->_response = $e->getResponse();

            
            throw new FrappeException(
                $this->_response->getReasonPhrase(), 
                $this->_response->getStatusCode()
            );
            
        }
        catch(ClientException $e)
        {
            $this->_response = $e->getResponse();

            throw new FrappeException(
                $this->_response->getReasonPhrase(), 
                $this->_response->getStatusCode()
            );
        }

        return $this;
    }

    public function ping()
    {
        $_status = false;
        try {
            
                $response = $this->httpClient->request('POST',
                            $this->frappeUrlApi."/frappe.auth.get_logged_user",
                            [
                                'headers'=>$this->_authHeader
                            ]
                        );
        
       
                if($response->getStatusCode()==200){
                    $this->_contents = $response->getBody()->getContents();
                   $_status = true;
                }elseif ($response->getStatusCode()==403){
                    $this->_contents = json_decode($response->getBody()->getContents());
                   $_status = false;
                }
        
       

        }
        catch(ClientException $e){

            $this->_response = $e->getResponse()->getBody()->getContents();

            $_status = false;            
        }

        return json_encode([
            'status'=>$_status,
            'response'=>$this->_contents
        ]);
        
    }

}