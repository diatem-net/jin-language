<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Language;

/**
 * Classe permettant la gestion de traductions d'items
 */
class Translation
{

  /**
   * @var string  Dossiers de stockage des traductions
   */
  protected static $storages = array();

  /**
   * @var string  Code de la langue active (fr par défaut)
   */
  protected static $languageCode = 'fr';

  /**
   * @var array   Traductions
   */
  protected static $translations = array();

  /**
   * @var array   Fichiers chargés
   */
  protected static $files = array();

  /**
   * Définit un autre dossier pour stocker les traductions (provoque le rechargement des fichiers déjà chargés)
   *
   * @param  string  $storage    Dossier de stockage
   * @param  integer $priority   (optional) Priorité (10 par défaut)
   */
  public static function addStorage($storage, $priority = 10)
  {
    self::$storages[$priority][] = realpath(rtrim($storage, '/'));
    self::$storages[$priority] = array_unique(self::$storages[$priority]);
    ksort(self::$storages);
    self::$translations = array();
    foreach(self::$files as $file) {
      static::loadFileInMemory($file);
    }
  }

  /**
   * Définit un autre code de langue (provoque le rechargement des fichiers déjà chargés)
   *
   * @param  string $languageCode  Code langue
   */
  public static function setLanguageCode($languageCode)
  {
    self::$languageCode = $languageCode;
    self::$translations = array();
    foreach(self::$files as $file) {
      static::loadFileInMemory($file);
    }
  }

  /**
   * Retourne le code langue courant
   *
   * @return string
   */
  public static function getLanguageCode()
  {
    return self::$languageCode;
  }

  /**
   * Charge un nouveau fichier de langue.
   *
   * @param  string $fileName  Nom du fichier INI à charger (sans les répertoires devant) ex. monfichier.ini
   * @return boolean           TRUE si succès
   */
  public static function loadFile($fileName)
  {
    if (!preg_match('/\.ini$/i', $fileName)) {
      $fileName .= '.ini';
    }
    if (!static::isFileLoaded($fileName)) {
      self::$files[] = $fileName;
      return static::loadFileInMemory($fileName);
    }
    return false;
  }

  /**
   * Permet de savoir si un fichier est déjà requis
   *
   * @param  string $fileName  Nom du fichier INI à charger (sans les répertoires devant) ex. monfichier.ini
   * @return boolean           TRUE si succès
   */
  public static function isFileLoaded($fileName)
  {
    if (!preg_match('/\.ini$/i', $fileName)) {
      $fileName .= '.ini';
    }
    return array_search($fileName, self::$files) !== false;
  }

  /**
   * Charge un nouveau fichier en mémoire (avec le code langue courant)
   *
   * @param  type $fileName  Nom du fichier INI à charger (sans les répertoires devant) ex. monfichier.ini
   * @return boolean         TRUE si succès
   * @throws \Exception
   */
  protected static function loadFileInMemory($fileName)
  {
    if (!preg_match('/\.ini$/i', $fileName)) {
      $fileName .= '.ini';
    }
    $found = 0;
    foreach (self::$storages as $priority => $substorages) {
      foreach ($substorages as $storage) {
        $data = parse_ini_file(sprintf('%s/%s/%s', $storage, self::$languageCode, $fileName));
        if ($data) {
          self::$translations = array_merge(self::$translations, $data);
          $found++;
        }
      }
    }
    return $found != 0;
  }

  /**
   * Renvoie la traduction d'un item défini, pour le code langue courant
   *
   * @param string $code  Code de l'item
   * @return mixed
   */
  public static function get($code)
  {
    if (array_key_exists($code, self::$translations)) {
      return self::$translations[$code];
    }
    return sprintf('[%s]', $code);
  }

}
