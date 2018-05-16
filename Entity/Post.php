<?php

namespace Bkstg\NoticeBoardBundle\Entity;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\SearchBundle\Model\SearchableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use MidnightLuke\GroupSecurityBundle\Model\GroupInterface;

/**
 * Post
 */
class Post implements SearchableInterface
{
    private $id;
    private $body;
    private $pinned;
    private $status;
    private $expiry;
    private $created;
    private $updated;
    private $author;
    private $groups;
    private $parent;
    private $children;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Post
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Post
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set expiry
     *
     * @param \DateTime $expiry
     *
     * @return Post
     */
    public function setExpiry(\DateTime $expiry = null)
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Get expiry
     *
     * @return \DateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Post
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Post
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Post
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add group
     *
     * @param Production $group
     *
     * @return Post
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$group instanceof Production) {
            throw new Exception('Group type not supported.');
        }
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param Production $group
     */
    public function removeGroup(GroupInterface $group)
    {
        if (!$group instanceof Production) {
            throw new Exception('Group type not supported.');
        }
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup(GroupInterface $group)
    {
        foreach ($this->groups as $my_group) {
            if ($group->isEqualTo($my_group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set pinned
     *
     * @param boolean $pinned
     *
     * @return Post
     */
    public function setPinned($pinned)
    {
        $this->pinned = $pinned;

        return $this;
    }

    /**
     * Get pinned
     *
     * @return boolean
     */
    public function getPinned()
    {
        return $this->pinned;
    }

    /**
     * Set parent
     *
     * @param Post $parent
     *
     * @return Post
     */
    public function setParent(Post $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Post
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param Post $child
     *
     * @return Post
     */
    public function addChild(Post $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Post $child
     */
    public function removeChild(Post $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function __toString()
    {
        return $this->body;
    }
}
