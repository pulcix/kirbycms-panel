<?php

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

class check {

  static function all() {
    
    $result      = array();
    $permissions = self::permissions();
    $blueprints  = self::blueprints();
    $thumbs      = self::thumbsAreWritable();

    if(!empty($permissions)) $result['permissions'] = array();
    if(!empty($blueprints))  $result['blueprints']  = $blueprints;
    if(!$thumbs)             $result['thumbs']      = array();
    
    return $result;
            
  }

  static function permissions() {
    
    global $site;
    
    $index  = $site->pages->index();
    $errors = array();  
    $base   = basename(c::get('root.content'));
    
    if(!is_writable(c::get('root.content')) && !@chmod(c::get('root.content'), 0755)) $errors[] = str_replace(c::get('root.content'), '/' . $base, c::get('root.content'));
    
    foreach($index as $dir) {
      if(!is_writable($dir->root()) && !@chmod($dir->root(), 0755)) $errors[] = str_replace(c::get('root.content'), '/' . $base, $dir->root());
    }
        
    return $errors;  

  }
  
  static function thumbsAreWritable() {
    $thumbs = c::get('root') . '/thumbs';
    return (is_dir($thumbs) && is_writable($thumbs)) ? true : false;    
  }
  
  static function infoIsWritable() {
    $file = c::get('root.content') . '/site.txt';
    return (is_writable($file)) ? true : false;
  }
  
  static function blueprints() {
    
    $templates  = dir::read(c::get('root.templates'));
    $blueprints = dir::read(c::get('root.site') . '/' . c::get('panel.folder') . '/blueprints');

    return array_diff($templates, $blueprints);
        
  }

  static function installed() {
    $dir = c::get('root.site') . '/' . c::get('panel.folder');
    
    if(!is_dir($dir)) return false;
    if(!is_dir($dir . '/accounts'))   return false;
    if(!is_dir($dir . '/blueprints')) return false;
    if(!is_dir($dir . '/config'))     return false;
    
    return true;    
        
  }

  static function hasAccounts() {

    $dir   = c::get('root.site') . '/' . c::get('panel.folder') . '/accounts';
    $files = dir::read($dir);
    
    return (empty($files)) ? false : true;
                
  }
  
  static function stillHasDefaultAccount() {
        
    $file = c::get('root.site') . '/' . c::get('panel.folder') . '/accounts/admin.php';

    if(file_exists($file)) return true;

    $dir     = c::get('root.site') . '/' . c::get('panel.folder') . '/accounts';
    $files   = dir::read($dir);
    $default = array(
      'username' => 'admin',
      'password' => 'adminpassword',
      'language' => 'en'
    ); 
    
    foreach($files as $file) {
      $username = f::name($file);
      $user     = user::load($username);
      $diff     = array_diff($user, $default);
      if(empty($diff)) return true;
    }
    
    return false;

  }
  
  static function wrongKirbyVersion() {
    return (c::get('version.number') < 1.06) ? true : false;
  }

  static function disabledRewrite() {
    return (c::get('rewrite') == false) ? true : false;
  }

}

?>