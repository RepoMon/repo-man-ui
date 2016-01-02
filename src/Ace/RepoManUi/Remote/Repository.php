<?php namespace Ace\RepoManUi\Remote;

/**
 * Represents a git repository
 *
 * @author timrodger
 * Date: 02/01/16
 */
class Repository
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $dependency_manager = '';

    /**
     * @var boolean
     */
    private $active = false;

    /**
     * @var string
     */
    private $timezone = '';

    /**
     * @param string $url
     * @param string $description
     * @param string $language
     */
    public function __construct($url, $description, $language)
    {
        $this->url = $url;
        $this->description = $description;
        $this->language = $language;
        $this->extractDependencyManager($language);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return trim(parse_url($this->getUrl(), \PHP_URL_PATH), '/');
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        $names = explode('/', $this->getFullName());
        return $names[0];
    }

    /**
     * @return string
     */
    public function getName()
    {
        $names = explode('/', $this->getFullName());
        return $names[1];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getDependencyManager()
    {
        return $this->dependency_manager;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @param string $language
     */
    private function extractDependencyManager($language)
    {
        switch (strtolower($language)) {
            case 'php':
                $this->dependency_manager = 'composer';
                break;
            case 'javascript':
                $this->dependency_manager = 'npm';
                break;
        }
    }
}