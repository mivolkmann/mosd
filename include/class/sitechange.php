<?php

class Seitenwechsel
{
   private $url,
   $suffix = '',
   $sc = 0,
   $f_l = array(),
   $f_a = array();

   public $gs, $ge, $a_s, $e_p_s;

   public function __construct($url, $f_l, $f_a, $ge, $e_p_s, $a_s = 0)
   {
      $this->url = $url;
      $this->gs = ceil($ge / $e_p_s);
      $this->ge = $ge;
      $this->a_s = ($a_s >= $this->gs || $a_s <= 0 || empty($a_s)) ? 0 : $a_s;
      $this->f_a = explode('?', $f_a);
      $this->f_l = explode('?', $f_l);
      $this->e_p_s = $e_p_s;
   }

   public function seite()
   {
      if($this->sc == $this->gs)
      {
         return false;
      }
      else if($this->a_s != $this->sc)
      {
         $ret = '<a href="'.$this->url.'/'.$this->sc.$this->suffix.'" title="Seite '.($this->sc + 1).'">'.$this->f_l[0].($this->sc + 1).$this->f_l[1].'</a>';
      }
      else
      {
         $ret = $this->f_a[0].($this->sc + 1).$this->f_a[1];
      }

      $this->sc++;
      return $ret;
   }

   public function siteChange()
   {
      $ret = '';

      if($this->gs > 25)
      {
         if($this->a_s - 5 > 0)
         {
            for($i = 0; $i < 5; $i++)
            {
               $ret .= $this->seite();
            }

            if($this->a_s - 6 < 5)
            {
               $this->sc = 5;
            }
            else
            {
               $ret .= '...';
               $this->sc = $this->a_s;
            }

            $this->sc = $this->a_s - 6 < 5 ? 5 : $this->a_s - 5;
         }
         else
         {
            $this->sc = $this->a_s - 6 < 0 ? 0 : $this->a_s - 6;
         }

         for($i = 0; $i < 11; $i++)
         {
            $ret .= $this->seite();
         }

         if($this->a_s + 6 < $this->gs)
         {
            $ret .= '...';
            $this->sc = $this->gs - 5;
         }

         for($i = 0; $i < 5; $i++)
         {
            $ret .= $this->seite();
         }
      }
      else
      {
         while($r = $this->seite())
         {
            $ret .= $r;
         }
      }

      return $ret;
   }

   public function setUrlSuffix($suffix)
   {
      $this->suffix = $suffix;
   }

   public function von()
   {
      return $this->a_s * $this->e_p_s + 1;
   }

   public function bis()
   {
      return (($this->a_s * $this->e_p_s + $this->e_p_s) > $this->ge) ? $this->ge : ($this->a_s * $this->e_p_s + $this->e_p_s);
   }

   public function reset()
   {
      $this->sc = 0;
   }
}

?>