<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
 if(!function_exists('hvac_sample_pluginFunction')) {

   function hvac_sample_plugin_write($note_data){
       $db = DB::getInstance();
       $pass_fail =  $db->insert("us_plugin_hvac_ref", ["note"=>$note_data]);
//       if ($pass_fail) {
//           db->
//       }
       return $pass_fail;
   }

     function hvac_sample_plugin_read(){

         $ch = curl_init("http://swquotesapi.digitaljedi.dk/api/SWQuote/RandomStarWarsQuote");

         curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
         curl_setopt($ch, CURLOPT_HEADER, 0);

         $response = curl_exec($ch);
         if(curl_error($ch)) {
             return  curl_error($ch);
         }
         curl_close($ch);

         $quote_object = json_decode($response);
         return $quote_object->starWarsQuote;
     }

 }
