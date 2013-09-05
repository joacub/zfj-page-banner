<?php
namespace ZfjPageBanner\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="zfj_page_banner_images")
 * use repository for handy tree functions
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Images
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FileBank\Entity\File")
     * @var ArrayCollection
     */
    protected $image;
    
    /**
     * @ORM\ManyToOne(targetEntity="ZfjPageBanner\Entity\PageBanner", inversedBy="images")
     */
    protected $page;
    
    /**
     * 
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $type;
    
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="PageBanner", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="PageBanner", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    public function getId()
    {
        return $this->id;
    }

    
    public function getLevel()
    {
    	return $this->lvl;
    }

    public function setParent(PageBanner $parent = null)
    {
        $this->parent = $parent;    
    }

    public function getParent()
    {
        return $this->parent;   
    }
    
    public function setCollector($collector)
    {
    	$this->collector = $collector;
    }
    
    public function getCollector()
    {
    	return $this->collector;
    }
	/**
	 * @return the $referenceId
	 */
	public function getReferenceId ()
	{
		return $this->referenceId;
	}

	/**
	 * @param field_type $referenceId
	 */
	public function setReferenceId ($referenceId)
	{
		$this->referenceId = $referenceId;
	}
	/**
	 * @return the $image
	 */
	public function getImage ()
	{
		return $this->image;
	}

	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection $image
	 */
	public function setImage ($image)
	{
		$this->image = $image;
	}

	/**
	 * @return the $page
	 */
	public function getPage ()
	{
		return $this->page;
	}

	/**
	 * @param field_type $page
	 */
	public function setPage ($page)
	{
		$this->page = $page;
	}
	/**
	 * @return the $type
	 */
	public function getType ()
	{
		return $this->type;
	}

	/**
	 * @param field_type $type
	 */
	public function setType ($type)
	{
		$this->type = $type;
	}




}
