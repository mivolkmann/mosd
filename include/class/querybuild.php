<?php

class QueryBuild
{
   private
   $queryString, $paramAnzahlGesamt, $param, $connection,
   $paramAnzahlAktuell = 0;

   public function __construct($queryString, $paramAnzahlGesamt, $connection)
   {
      preg_match_all('#\?#', $queryString, $res);

      if(count($res[0]) !== $paramAnzahlGesamt)
      {
         die($this->_getError(1));
      }

      $this->connection = $connection;
      $this->queryString = $queryString;
      $this->paramAnzahlGesamt = $paramAnzahlGesamt;
   }

   public function setParam($wert, $typ)
   {
      if($this->paramAnzahlAktuell + 1 > $this->paramAnzahlGesamt)
      {
         die($this->_getError(2));
      }

      switch($typ)
      {
         case 'str':
            $this->param[] = ZF::sqlString($wert, $this->connection);
            break;

         case 'int':
            $this->param[] = ZF::sqlInteger($wert);
            break;

         case 'double':
            $this->param[] = ZF::sqlDouble($wert);
            break;

         default:
            die($this->_getError(3));
      }

      $this->paramAnzahlAktuell++;
   }

   public function exec()
   {
      if($this->paramAnzahlAktuell != $this->paramAnzahlGesamt)
      {
         die($this->_getError(4));
      }

      $result = '';
      $token = explode('?', $this->queryString);
      $tokenAnz = count($token);

      for($i = 0; $i < $tokenAnz; $i++)
      {
         $result .= $token[$i].' '.(isset($this->param[$i]) ? $this->param[$i] : '');
      }

      return $result;
   }

   private static function _getError($errorId)
   {
      switch($errorId)
      {
         case 1: return 'Unstimmige Parameterzahl in einem Query';
         case 2: return 'Es wurden bereits alle Parameter hinzugefügt';
         case 3: return 'Es wurde ein unbekannter Parametertyp angegeben';
         case 4: return 'Die angegebene Parameterzahl des Queries wurde noch nicht erreicht';
         default: return 'Es ist ein unbekannter Fehler aufgetreten';
      }
   }
}

?>