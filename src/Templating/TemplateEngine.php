<?php

namespace Wizard\Src\Templating;

use Wizard\Src\Templating\Exception\TemplateEngineException;

class TemplateEngine
{
    /**
     * @var $view_path
     * Holds the path where all the views are stored.
     */
    public $view_path;

    /**
     * TemplateEngine constructor.
     * @param $view_path
     *
     * Set the view path
     */
    public function __construct($view_path)
    {
        $this->view_path = $view_path;
    }

    /**
     * @param $content
     * @return mixed
     * @throws TemplateEngineException
     *
     * Parses an page to check it for special tags.
     */
    public function parse($content)
    {
        $content = $this->removeHTMLComments($content);

        $is_using_other = $this->isUsingOtherFile($content);

        if ($is_using_other !== false) {
            $content = $this->parseUsingTemplate($content, $is_using_other);
        }
        $content = $this->includeIncludes($content, $this->searchIncludes($content));

        $content = $this->parsePHPTags($content);

        return $content;
    }

    /**
     * @param $content
     * @return bool
     *
     * Determines if the string that is loaded uses another file as master file.
     */
    private function isUsingOtherFile($content)
    {
        $pattern = '/(\[\{use)@[\w.\/]+(\}\])/';
        if (preg_match($pattern, $content, $match) === 1) {
            return $match[0];
        }
        return false;
    }

    /**
     * @param $content
     * @return mixed
     *
     * Removes all the html comments from a string.
     */
    private function removeHTMLComments($content)
    {
        //$pattern = '/(\/\*).+(\*\/)/'; Pattern for php multi line comments
        $pattern = '/(<!--).+(-->)/';
        return preg_replace($pattern, '', $content);
    }

    /**
     * @param $content
     * @return mixed
     *
     * Searches for fillable tags in a string.
     */
    private function findFillableTags($content)
    {
        $pattern = '/(\[\{fillable)@[\w]+(\}\])/';
        preg_match_all($pattern, $content, $matches);
        return $matches[0];
    }

    /**
     * @param $content
     * @param $tag
     * @return bool
     *
     * Find the tags that fill a fillable tag.
     */
    private function findFillingTag($content, $tag)
    {
        $pattern = '/(\[\{fill@'.$tag.'\}\]).*(\[\{end_fill@'.$tag.'\}\])/s';
        if (preg_match($pattern, $content, $match) === 1) {
            return $match[0];
        }
        return false;
    }

    /**
     * @param $content
     * @return mixed
     *
     * Searches through a string for any includes from another file.
     */
    private function searchIncludes($content)
    {
        $pattern = '/(\[\{include)@[\w\/]+@[\w]+(\}\])/';
        preg_match_all($pattern, $content, $matches);
        return $matches[0];
    }

    /**
     * @param $content
     * @param $includes
     * @return mixed
     * @throws TemplateEngineException
     *
     * Loops through the array of include tags and include the matching section.
     */
    private function includeIncludes($content, $includes)
    {
        foreach ($includes as $include) {
            $exploded = explode('@', $include);
            $path = $this->view_path . $exploded[1] . '.template.php';
            if (!file_exists($path)) {
                throw new TemplateEngineException('Section file path doesnt exist');
            }
            $section = explode('}]', $exploded[2])[0];
            $section_file = file_get_contents($path);
            $section_content = $this->searchSection($section_file, $section);
            if ($section_content === false) {
                throw new TemplateEngineException('Section not found');
            }
            $section_content = str_replace('[{section@'.$section.'}]', '', $section_content);
            $section_content = str_replace('[{end_section@'.$section.'}]', '', $section_content);
            $content = str_replace($include, $section_content, $content);
        }
        return $content;
    }

    /**
     * @param $content
     * @param $tag
     * @return mixed
     * @throws TemplateEngineException
     *
     * Set the usage of a master template.
     */
    private function parseUsingTemplate($content, $tag)
    {
        $file = substr($tag, strpos($tag, '@') + 1);
        $file = $this->view_path. substr($file, 0, strlen($file) - 2). '.template.php';
        if (!file_exists($file)) {
            throw new TemplateEngineException('File that has to be used doesnt exist');
        }
        $master = file_get_contents($file);
        if ($this->isUsingOtherFile($master) !== false) {
            throw new TemplateEngineException('Cant use other files multiple times');
        }
        $fillable_tags = $this->findFillableTags($master);
        foreach ($fillable_tags as $tag) {
            $stripped_tag = substr($tag, strpos($tag, '@') + 1);
            $stripped_tag = substr($stripped_tag, 0, strlen($stripped_tag) - 2);
            $tag_content = $this->findFillingTag($content, $stripped_tag);
            if ($tag_content !== false) {
                $tag_content = str_replace('[{fill@'.$stripped_tag.'}]', '', $tag_content);
                $tag_content = str_replace('[{end_fill@'.$stripped_tag.'}]', '', $tag_content);
                $master = str_replace($tag, $tag_content, $master);
            } else {
                $master = str_replace($tag, '', $master);
            }
        }
        return $master;
    }

    /**
     * @param $content
     * @param $section
     * @return bool
     *
     * Search through a string for sections.
     */
    private function searchSection($content, $section)
    {
        $pattern = '/(\[\{section@'.$section.'\}\]).*(\[\{end_section@'.$section.'\}\])/s';
        if (preg_match($pattern, $content, $match) === 1) {
            return $match[0];
        }
        return false;
    }

    /**
     * @param $content
     * @return mixed
     *
     * Replaces special tags with php tags.
     */
    private function parsePHPTags($content)
    {
        $content = str_replace('[{', '<?php', $content);
        $content = str_replace('}]', '?>', $content);
        $content = str_replace('{{', '<?=', $content);
        $content = str_replace('}}', '?>', $content);
        return $content;
    }
}












