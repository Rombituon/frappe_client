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
    private $httpMethod;
    private $frappeBuildUrlResource;
    private $_contents;
    private $_authHeader;
    private $_response;
    public function __construct()
    {
        

        $this->token = config("frappe.api_token");
        $this->frappeUrlApi = config("frappe.url") . "/api/method";
        $this->frappeUrlResource = config("frappe.url") . "/api/resource";
        $this->frappeVersion = config('frappe.version');
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

        if(isset($options['sort_by'])){
            $query['sort_by'] = json_encode($options['sort_by']);
        }

        if($this->frappeVersion=="14.*"){
            if(isset($options['as_dict'])){
                if($options['as_dict'] == 'False'){
                    $query['as_dict'] = json_encode($options['as_dict']);
                }  
            }
        }
        
        
        if($query){
            $url .= '?';
            foreach($query as $key => $value){
                $url .= $key.'='.$value.'&';
            }
        }

        // dd($url);

        return $url;
    }


    /**
     * Builds the url for frappe resource
     * @param string $url
     * @param string $docName
     * @param string $crudType "R" for read, "U" for update, "D" for delete, "C" for create
     * @return string
     */
    private function _buildUriForFrappeCRUDResource($url, $docName, $crudType="R")
    {
        if($crudType=="R" || $crudType=="U" || $crudType=="D"){

            if($docName){
               $url .= "/".$docName;
               return $url; 
            }

            $url .= "/".$docType;
            return $url; 
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

    public function create($data)
    {
        try{
            if($this->httpMethod=='POST'){

                $response = $this->httpClient->request($this->httpMethod,
                                $this->frappeBuildUrlResource,
                                [
                                    'headers'=>$this->_authHeader,
                                    'json'=>$data
                                ]
                            );

                $res = json_decode($response
                    ->getBody()
                    ->getContents());

                return $res->data;
            }

            throw new FrappeException(
                "Accept only POST Method", 
                "405"
            );
        }
        catch(ServerException $e){



            $this->_response = $e->getResponse();

            // dd($e->getResponse()->getBody()->getContents());

            
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
    }

    public function delete()
    {
        try{
            if($this->httpMethod=="DELETE"){

                $response = $this->httpClient->request($this->httpMethod,
                                $this->frappeBuildUrlResource,
                                [
                                    'headers'=>$this->_authHeader
                                ]
                            );

                $res = json_decode($response
                    ->getBody()
                    ->getContents());

                return $res->data;
            }

            throw new FrappeException(
                "Accept only DELETE Method", 
                "405"
            );
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
    }

    public function update($data)
    {

        
        try{
            if($this->httpMethod=='PUT'){

                $response = $this->httpClient->request($this->httpMethod,
                                $this->frappeBuildUrlResource,
                                [
                                    'headers'=>$this->_authHeader,
                                    'json'=>$data
                                ]
                            );

                $res = json_decode($response
                    ->getBody()
                    ->getContents());

                return $res->data;
            }

            throw new FrappeException(
                "Accept only PUT Method", 
                "405"
            );
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
        
        
    }

    public function toJson()
    {
        $res = json_decode($this->
                _response
                ->getBody()
                ->getContents());

        return json_encode($res->data);
    }

    public function resource($docType, $docName=null, $options=[], $crudType="R")
    {
        $url = $this->frappeUrlResource . '/' . $docType;

        if($crudType=="R"){
            $this->httpMethod = "GET";
        }
        elseif($crudType=="C"){
            $this->httpMethod = "POST";
        }
        elseif($crudType=="U"){
            $this->httpMethod = "PUT";
        }
        elseif($crudType=="D"){
            $this->httpMethod = "DELETE";
        }
    
        try 
        {

            if($docName && $crudType !="C")
            {
                $buildUrl = $this->_buildUriForFrappeCRUDResource($url,$docName,$crudType);   
            }
            elseif($docName==null && $crudType == "C")
            {

                $buildUrl = $this->_buildUriForFrappeCRUDResource($url,null,$crudType);
            }
            else{
                $buildUrl = $this->_buildUriForFrappeResource($url,$options);  
                
                
            }

            $this->frappeBuildUrlResource = $buildUrl;

           
            // separate method
            if($crudType=='R'){

                $response = $this->httpClient->request($this->httpMethod,
                            $buildUrl,
                            [
                                'headers'=>$this->_authHeader
                            ]
                        );

                $this->_response = $response;
            }
            else
            {
                return $this;
            }

            

            
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