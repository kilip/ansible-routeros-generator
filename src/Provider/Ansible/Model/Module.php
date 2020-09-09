<?php


namespace RouterOS\Generator\Provider\Ansible\Model;


use RouterOS\Generator\Model\SubMenu;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Module
 *
 * @ORM\Entity()
 * @ORM\Table(name="ansible.module")
 * @package RouterOS\Generator\Provider\Ansible\Model
 */
class Module
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="RouterOS\Generator\Model\SubMenu")
     *
     * @var SubMenu
     */
    private $subMenu;

    /**
     * @var string
     */
    private $configFile;

    /**
     * @var array
     */
    private $config = [];

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return SubMenu
     */
    public function getSubMenu(): SubMenu
    {
        return $this->subMenu;
    }

    /**
     * @param SubMenu $subMenu
     * @return static
     */
    public function setSubMenu(SubMenu $subMenu)
    {
        $this->subMenu = $subMenu;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * @param string $configFile
     * @return static
     */
    public function setConfigFile(string $configFile)
    {
        $this->configFile = $configFile;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return static
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }
}