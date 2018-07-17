<?php
 require_once  'minify/jsmin.php';
 require_once  'minify/css/Minify.php';
 require_once  'minify/css/CSS.php';
 require_once  'minify/css/Converter.php';

 class SoconfigMinifier
 {
    private $path;
    //catalog\view\javascript\soconfig
  public function __construct() {
    if(!defined('SOCONFIG_CACHE_DIR')) define('SOCONFIG_CACHE_DIR',dirname(DIR_APPLICATION).'/system/library/so/cache/');
    $this->path = SOCONFIG_CACHE_DIR.'minify/';
    
   }
	public function get_compliled_css_file_path($files){
		$combined_css_file = $this->get_file_name($files,'css');
		
		if (!file_exists($this->path . $combined_css_file)) {
			
			foreach ($files as $style) {
				
				$file = $this->get_file_name($style, 'css');
			
				if (!file_exists($file)) {
				   $css_file = realpath(dirname(DIR_APPLICATION) . '/' . $this->clean($style));
				   if (!file_exists($css_file)) continue;
					$minifier = new CSS();  
					$minifier->add($css_file);
					$minifier->minify(SOCONFIG_CACHE_DIR.'minify/'.$file);  
				}
		   }
		   $handler = @fopen($this->path.$combined_css_file, 'w');
		   flock($handler, LOCK_EX);
		   
		   foreach ($files as $style) 
		   {
			  $file = SOCONFIG_CACHE_DIR.'minify/'.$this->get_file_name($style, 'css');
			  if (!file_exists($file)) continue;
			  
			  $content = file_get_contents($file);
			  fwrite($handler, $content);
		   }
		   flock($handler, LOCK_UN);
		   fclose($handler);
		}

       
     return $combined_css_file;
   }
   
   public function get_compliled_js_file_path($files)
   {
     $combined_js_file = $this->get_file_name($files,'js');
     
     if (!file_exists($this->path . $combined_js_file)) 
     {
       foreach ($files as $script) 
       {
         $file = $this->path.$this->get_file_name($script, 'js');
         
         if (!file_exists($file)) 
         {
           $js_file = realpath(dirname(DIR_APPLICATION) . '/' . $this->clean($script));
           if (!file_exists($js_file)) 
             continue;
           
           $content = file_get_contents($js_file);
           
           $content = JSMin::minify($content);
           
          file_put_contents($file, $content, LOCK_EX);
         }
       }
       
       $handler = @fopen($this->path.$combined_js_file, 'w');
       flock($handler, LOCK_EX);
       
       foreach ($files as $script) 
       {
          $file = $this->path.$this->get_file_name($script, 'js');
          if (!file_exists($file)) 
          {
              continue;
          }
          $content = file_get_contents($file);
          fwrite($handler, $content);
       }
       flock($handler, LOCK_UN);
       fclose($handler);
     }
     
     return $combined_js_file;
   }
   
    private function get_file_name($files,$ext){
        $hash = '';
        if (is_array($files))  {
            foreach ($files as $file)  $hash .= $file;
            
        } else{
            $hash = $files;
        } 
     return md5($hash).'.'.$ext;    
   }
   
   private function clean($file) {
     $file = explode('?', $file);
     return $file[0];
   }
   
 }