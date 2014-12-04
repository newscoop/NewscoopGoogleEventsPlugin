<?php
/**
 * @package Newscoop\GoogleEventsPluginBundle
 * @author Mark Lewis <mark.lewis@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\GoogleEventsPluginBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * GoogleEvent entity
 *
 * @ORM\Entity(repositoryClass="Newscoop\GoogleEventsPluginBundle\Repository\GoogleEventRepository")
 * @ORM\Table(name="plugin_google_event")
 */
class GoogleEvent
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255, name="id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text",  name="kind")
     * @var string
     */
    protected $kind;

    /**
     * @ORM\Column(type="text",  name="etag")
     * @var string
     */
    protected $etag;

    /**
     * @ORM\Column(type="text",  name="status")
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="text", name="htmlLink")
     * @var string
     */
    protected $htmlLink;

    /**
     * @ORM\Column(type="text",  name="summary")
     * @var string
     */
    protected $summary;

    /**
     * @ORM\Column(type="text",  name="description", nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="text",  name="location", nullable=true)
     * @var string
     */
    protected $location;

    /**
     * @ORM\Column(type="string", length=255, name="creator_email")
     * @var string
     */
    protected $creatorEmail;

    /**
     * @ORM\Column(type="string", length=255, name="creator_display_name")
     * @var string
     */
    protected $creatorDisplayName;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var datetime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     * @var datetime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="start")
     * @var datetime
     */
    protected $start;

    /**
     * @ORM\Column(type="datetime", name="end")
     * @var datetime
     */
    protected $end;

    /**
     * @ORM\Column(type="text", name="json")
     * @var string
     */
    protected $json;

    /**
     * @ORM\Column(type="datetime", name="imported_at")
     * @var datetime
     */
    protected $importedAt;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     * @var boolean
     */
    protected $isActive;

    public function __construct()
    {
        $this->setImportedAt(new \DateTime());
        $this->setIsActive(true);
    }

    /**
     * Gets the value of id.
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param string $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of kind.
     *
     * @return string 
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Sets the value of kind.
     *
     * @param string $kind the kind
     *
     * @return self
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Gets the value of etag.
     *
     * @return string 
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * Sets the value of etag.
     *
     * @param string $etag the etag
     *
     * @return self
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets the value of htmlLink.
     *
     * @return string 
     */
    public function getHtmlLink()
    {
        return $this->htmlLink;
    }

    /**
     * Sets the value of htmlLink
     *
     * @param string $htmlLink the htmlLink
     *
     * @return self
     */
    public function setHtmlLink($htmlLink)
    {
        $this->htmlLink = $htmlLink;

        return $this;
    }

    /**
     * Gets the value of summary.
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Sets the value of summary
     *
     * @param string $summary the summary
     *
     * @return self
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Gets the value of description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description 
     *
     * @param string $description the description 
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of location 
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the value oflocation 
     *
     * @param string $location the location 
     *
     * @return self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Gets the value of creatorEmail 
     *
     * @return string 
     */
    public function getCreatorEmail()
    {
        return $this->creatorEmail;
    }

    /**
     * Sets the value of creatorEmail
     *
     * @param string $creatorEmail the creatorEmail 
     *
     * @return self
     */
    public function setCreatorEmail($creatorEmail)
    {
        $this->creatorEmail = $creatorEmail;

        return $this;
    }

    /**
     * Gets the value of creatorDisplayName
     *
     * @return string 
     */
    public function getCreatorDisplayName()
    {
        return $this->creatorDisplayName;
    }

    /**
     * Sets the value of creatorDisplayName
     *
     * @param string $creatorDisplayName the creatorDisplayName
     *
     * @return self
     */
    public function setCreatorDisplayName($creatorDisplayName)
    {
        $this->creatorDisplayName = $creatorDisplayName;

        return $this;
    }

    /**
     * Gets the value of json.
     *
     * @return string 
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Sets the value of json.
     *
     * @param string $json the json 
     *
     * @return self
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Gets the value of created_at.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of created_at.
     *
     * @param datetime $created_at the created  at
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the value of updated_at.
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updated_at.
     *
     * @param datetime $updated_at the updated  at
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    /**
     * Gets the value of isActive.
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Sets the value of isActive.
     *
     * @param boolean $isActive the is  active
     *
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Gets the value of imported_at.
     *
     * @return datetime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * Sets the value of imorted_at.
     *
     * @param datetime $imported_at the created  at
     *
     * @return self
     */
    public function setImportedAt(\DateTime $importedAt)
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    /**
     * Gets the value of start.
     *
     * @return datetime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets the value of start.
     *
     * @param datetime $start the start 
     *
     * @return self
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Gets the value of end.
     *
     * @return datetime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets the value of end.
     *
     * @param datetime $end the end 
     *
     * @return self
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }


}
