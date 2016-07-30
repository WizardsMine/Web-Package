<?php

namespace Wizard\Assets;

use Wizard\Kernel\App;
use Wizard\Modules\Config\Config;

class AssetsManager
{

    private $assets;

    private $file;

    public function __construct($assets_name = null)
    {
        $this->assets = $assets_name;
    }

    /**
     * Returning an array with 3 keys
     * css => an array with every exact html link tag
     * js => an array with exact html script tag
     * params => an array with variable name and value
     */
    public function load()
    {
        $assets = array('css' => array(), 'js' => array(), 'links' => array(), 'images' => array());
        try {
            if ($this->assets === null) {
                return $assets;
            }
            $available_names = $this->getAvailableAssets();

            if (is_array($this->assets)) {

                foreach ($this->assets as $asset) {
                    if (!is_string($asset)) {
                        throw new AssetException('Cant load a non string asset group');
                    }
                    if (!in_array($asset, $available_names, true)) {
                        throw new AssetException('Asset not found');
                    }
                    $assets = array_merge_recursive($assets, $this->loadAsset($asset));
                }

            } elseif (is_string($this->assets)) {

                if (!in_array($this->assets, $available_names, true)) {
                    throw new AssetException('Asset not found');
                }
                $assets = array_merge_recursive($assets, $this->loadAsset($this->assets));

            } else {
                throw new AssetException('Assets only accepting an array or string');
            }
            return $assets;
        } catch (AssetException $e) {
            $e->showErrorPage();
        }
    }

    /**
     * @param string $asset_name
     * @throws AssetException
     * @return array
     * Returning an array with 3 keys
     * css => an array with every exact html link tag
     * js => an array with exact html script tag
     * params => an array with variable name and value
     */
    public function loadAsset(string $asset_name)
    {
        $assets = array('css' => array(), 'js' => array(), 'links' => array(), 'images' => array());
        if (array_key_exists($asset_name, $this->file)) {
            $asset_info = $this->file[$asset_name];
        } else {
            foreach ($this->file as $configAsset) {
                if (array_key_exists('name', $configAsset) && $configAsset['name']  == $asset_name) {
                    $asset_info = $configAsset;
                }
            }
        }
        if (!isset($asset_info)) {
            return $assets;
        }

        if (array_key_exists('autoload', $asset_info)) {
            $assets = array_merge_recursive($assets, $this->autoload($asset_info['autoload']));
        }

        if (array_key_exists('images', $asset_info)) {
            $assets['images'] = array_merge_recursive($assets['images'], $this->getImages($asset_info['images']));
        }

        if (array_key_exists('links', $asset_info)) {
            $assets['links'] = array_merge_recursive($assets['links'], $this->getLinks($asset_info['links']));
        }
        return $assets;
    }

    /**
     * @param $autoload
     * @return array
     * @throws AssetException
     *
     * Returning an array with all the autoload tags that needs to be placed within the template.
     */
    public function autoload($autoload)
    {
        if (!is_array($autoload)) {
            throw new AssetException('Autoload value must be a array');
        }
        $assets = array('css' => array(), 'js' => array());
        foreach ($autoload as $path => $type) {
            if (!is_string($type)) {
                throw new AssetException('Asset file type must be a string');
            }
            if (!is_string($path)) {
                throw new AssetException('Asset path must be a string');
            }
            switch ($type) {
                case 'css':
                    if (substr($path, 0, 7) === 'http://' || substr($path, 0, 8) === 'https://') {
                        $assets['css'][] = htmlentities("<link rel='stylesheet' href='$path'>");
                        break;
                    }
                    if (!file_exists(App::$root.'/Resources/Assets/css/'.$path.'.css')) {
                        throw new AssetException('Asset css file not found');
                    }

                    $realPath = App::$base_uri.'/Resources/Assets/css/'.$path.'.css';
                    $assets['css'][] = htmlentities("<link rel='stylesheet' href='$realPath'>");
                    break;

                case 'js':
                    if (substr($path, 0, 7) === 'http://' || substr($path, 0, 8) === 'https://') {
                        $assets['js'][] = htmlentities("<script src='$path'></script>");
                        break;
                    }
                    if (!file_exists(App::$root.'/Resources/Assets/js/'.$path.'.js')) {
                        throw new AssetException('Asset css file not found');
                    }
                    $realPath = App::$base_uri.'/Resources/Assets/js/'.$path.'.js';
                    $assets['js'][] = htmlentities("<script src='$realPath'></script>");
                    break;

                default:
                    throw new AssetException('Unknown or unsupported autoload asset file type');
            }
        }
        return $assets;
    }

    /**
     * @param $images
     * @return array
     * @throws AssetException
     *
     * Get the image paths.
     */
    public function getImages($images)
    {
        if (!is_array($images)) {
            throw new AssetException('Autoload value must be a array');
        }
        $image_links = array();
        foreach ($images as $variable => $image) {
            if (!is_string($variable) || !is_string($image)) {
                throw new AssetException('Both variable and image path must be a string');
            }
            if (substr($image, 0, 7) === 'http://' || substr($image, 0, 8) === 'https://') {
                $image_links[$variable] = $image;
                continue;
            }
            if (!file_exists(App::$root.'/Resources/Assets/img/'.$image)) {
                throw new AssetException('Asset image file not found');
            }
            $image_links[$variable] = App::$base_uri.'/Resources/Assets/img/'.$image;
        }
        return $image_links;
    }

    /**
     * @param $links
     * @return array
     * @throws AssetException
     *
     * Validates asset links
     */
    public function getLinks($links)
    {
        if (!is_array($links)) {
            throw new AssetException('Links value must be an array');
        }
        $validated_links = array();
        foreach ($links as $variable => $link) {
            if (!is_string($variable) || !is_string($link)) {
                throw new AssetException('Both variable and link path must be a string');
            }
            if (substr($link, 0, 7) === 'http://' || substr($link, 0, 8) === 'https://') {
                $validated_links[$variable] = $link;
                continue;
            }
            if (!file_exists(App::$root.'/Resources/Assets/'.$link)) {
                throw new AssetException('Assets file not found '.$link);
            }
            $validated_links[$variable] = App::$base_uri. '/Resources/Assets/'.$link;
        }
        return $validated_links;
    }

    /**
     * @return array
     * @throws AssetException
     *
     * Returns a array with all the names of available assets.
     */
    public function getAvailableAssets()
    {
        $assetsFile = $this->getAssets();
        $this->file = $assetsFile;
        $names = array();
        foreach ($assetsFile as $name => $value) {
            if (!is_array($value)) {
                throw new AssetException('Only arrays are allowed as asset groups in the config');
            }
            if (is_string($name)) {
                $names[] = $name;
                continue;
            }
            if ($value['name'] ?? null !== null && is_string($value['name'])) {
                $names[] = $value['name'];
                continue;
            }
            throw new AssetException('Asset group found without a key or name value');
        }
        return $names;
    }

    /**
     * @return mixed|null
     * @throws AssetException
     *
     * Get the asset file and checks if its an array.
     */
    public function getAssets()
    {
        $file = Config::getFile('assets');
        if ($file === null) {
            throw new AssetException("Couldn't load assets file");
        }
        if (!is_array($file)) {
            throw new AssetException('Assets config file didnt return an array');
        }
        return $file;
    }
}










