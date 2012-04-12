<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class StreamOAuth
{
   public $http_code, $host, $http_info,
   $timeout = 30,
   $connecttimeout = 30,
   $ssl_verifypeer = false,
   $useragent = 'Chemnitz University of Technology';

   private
   $token, $urls;

   function __construct($host, $consumer_key, $consumer_secret, $oauth_token = '', $oauth_token_secret = '')
   {
      $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
      $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
      $this->host = $host;

      if(!empty($oauth_token) && !empty($oauth_token_secret))
      {
         $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
      }
      else
      {
         $this->token = null;
      }
   }

   function setURLs($authURL, $requestURL, $accessURL)
   {
      $this->urls = array('authURL' => $authURL,
                          'requestURL' => $requestURL,
                          'accessURL' => $accessURL);
   }

   function getRequestToken($oauth_callback = null)
   {
      $parameters = array();

      if(!empty($oauth_callback))
      {
         $parameters['oauth_callback'] = $oauth_callback;
      }

      $request = $this->oAuthRequest($this->urls['requestURL'], 'GET', $parameters);
      $token = OAuthUtil::parse_parameters($request);

      $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);

      return $token;
   }

   function getAuthorizeURL($token)
   {
      if(is_array($token))
      {
         $token = $token['oauth_token'];
      }

      return $this->urls['authURL'].'?oauth_token='.$token;
   }

   function getAccessToken($oauth_verifier = false)
   {
      $parameters = array();

      if(!empty($oauth_verifier))
      {
         $parameters['oauth_verifier'] = $oauth_verifier;
      }

      $request = $this->oAuthRequest($this->urls['accessURL'], 'GET', $parameters);

      $token = OAuthUtil::parse_parameters($request);
      $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);

      return $token;
   }

   function getXAuthToken($username, $password)
   {
      $parameters = array();
      $parameters['x_auth_username'] = $username;
      $parameters['x_auth_password'] = $password;
      $parameters['x_auth_mode'] = 'client_auth';
      $request = $this->oAuthRequest($this->urls['accessURL'], 'POST', $parameters);
      $token = OAuthUtil::parse_parameters($request);
      $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);

      return $token;
   }

   function get($url, $parameters = array())
   {
      $response = $this->oAuthRequest($url, 'GET', $parameters);

      return json_decode($response);
   }

   function post($url, $parameters = array())
   {
      $response = $this->oAuthRequest($url, 'POST', $parameters);

      return json_decode($response);
   }

   function delete($url, $parameters = array())
   {
      $response = $this->oAuthRequest($url, 'DELETE', $parameters);

      return json_decode($response);
   }

   function oAuthRequest($url, $method, $parameters)
   {
      if(strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0)
      {
         $url = $this->host.$url.'.json';
      }

      $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
      $request->sign_request($this->sha1_method, $this->consumer, $this->token);

      switch($method)
      {
         case 'GET':
            return $this->http($request->to_url(), 'GET');

         default:
            return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
      }
   }

   function http($url, $method, $postfields = null)
   {
      $this->http_info = array();
      $ci = curl_init();

      curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
      curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
      curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
      curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
      curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
      curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
      curl_setopt($ci, CURLOPT_HEADER, false);

      switch($method)
      {
         case 'POST':
            curl_setopt($ci, CURLOPT_POST, true);
            if(!empty($postfields))
            {
               curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
            }
            break;

         case 'DELETE':
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if(!empty($postfields))
            {
               $url = "{$url}?{$postfields}";
            }
      }

      curl_setopt($ci, CURLOPT_URL, $url);
      $response = curl_exec($ci);
      $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
      $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
      $this->url = $url;
      curl_close($ci);
      return $response;
   }

   function getHeader($ch, $header)
   {
      $i = strpos($header, ':');

      if(!empty($i))
      {
         $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
         $value = trim(substr($header, $i + 2));
         $this->http_header[$key] = $value;
      }

      return strlen($header);
   }

   function getStatusCode()
   {
      return $this->http_code;
   }

   function lastAPICall()
   {
      return $this->last_api_call;
   }
}

?>
