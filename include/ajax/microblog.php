<?php

   if($this->_post('url') && $this->_post('query'))
   {
      $this->_jsonOutput(1, json_decode(file_get_contents($this->_post('url').'search.json?q='.urlencode($this->_post('query')).'&since_id='.$this->_post('since').'&rpp='.$this->_post('rpp').'&result_type=recent')));
   }
   else
   {
      $this->_jsonOutput(2, 'wrong data set');
   }

?>