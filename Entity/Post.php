<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Entity;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\CoreBundle\Model\ExpirableInterface;
use Bkstg\CoreBundle\Model\PublishableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use MidnightLuke\GroupSecurityBundle\Model\GroupInterface;

class Post implements GroupableInterface, PublishableInterface, ExpirableInterface
{
    private $id;
    private $body;
    private $pinned;
    private $active;
    private $published;
    private $expiry;
    private $created;
    private $updated;
    private $author;
    private $groups;
    private $parent;
    private $children;

    /**
     * Create new post.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set body.
     *
     * @param string $body The body.
     *
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set active.
     *
     * @param bool $active The active state.
     *
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return true === $this->active;
    }

    /**
     * Set published.
     *
     * @param bool $published The published state.
     *
     * @return self
     */
    public function setPublished(bool $published): PublishableInterface
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return true === $this->published;
    }

    /**
     * Set expiry.
     *
     * @param ?\DateTimeInterface $expiry The expiry time.
     *
     * @return self
     */
    public function setExpiry(?\DateTimeInterface $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Get expiry.
     *
     * @return \DateTimeInterface
     */
    public function getExpiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }

    /**
     * Check if expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return null !== $this->expiry && $this->expiry < new \DateTime('now');
    }

    /**
     * Set created.
     *
     * @param \DateTimeInterface $created The created time.
     *
     * @return self
     */
    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTimeInterface
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTimeInterface $updated The updated time.
     *
     * @return self
     */
    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTimeInterface
     */
    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * Set author.
     *
     * @param string $author The author to set.
     *
     * @return self
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $group The group to add.
     *
     * @throws \Exception If the group is not a production.
     *
     * @return self
     */
    public function addGroup(GroupInterface $group): self
    {
        if (!$group instanceof Production) {
            throw new \Exception(sprintf('The group type "%s" is not supported.', get_class($group)));
        }
        $this->groups[] = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $group The group to remove.
     *
     * @return self
     */
    public function removeGroup(GroupInterface $group): self
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Get groups.
     *
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $group The group to check for.
     *
     * @return bool
     */
    public function hasGroup(GroupInterface $group): bool
    {
        return $this->groups->contains($group);
    }

    /**
     * Set pinned.
     *
     * @param bool $pinned The pinned state.
     *
     * @return self
     */
    public function setPinned(bool $pinned): self
    {
        $this->pinned = $pinned;

        return $this;
    }

    /**
     * Get pinned.
     *
     * @return bool
     */
    public function getPinned(): bool
    {
        return true === $this->pinned;
    }

    /**
     * Set parent.
     *
     * @param ?Post $parent The parent post.
     *
     * @return self
     */
    public function setParent(?Post $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return Post
     */
    public function getParent(): ?Post
    {
        return $this->parent;
    }

    /**
     * Add child post.
     *
     * @param Post $child The child post to add.
     *
     * @return self
     */
    public function addChild(Post $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child post.
     *
     * @param Post $child The child to remove.
     *
     * @return self
     */
    public function removeChild(Post $child): self
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * Get children.
     *
     * @return Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Convert to a string.
     *
     * @return string
     */
    public function __toString(): ?string
    {
        return $this->body;
    }
}
