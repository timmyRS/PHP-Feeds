<?php

class Feed
{
	public $root;
	public $type = false;
	private function __construct($root)
	{
		$this->root = $root;
		if($this->root->getName() == "rss")
		{
			$this->type = "rss".$this->root["version"];
			$this->root = $this->root->channel;
		}
		else if($this->root->getName() == "feed")
		{
			$this->type = "atom";
		}
	}
	public static function fromFile($file)
	{
		return new Feed(simplexml_load_file($file));
	}
	public static function fromString($string)
	{
		return new Feed(simplexml_load_string($string));
	}
	public function isTypeSupported()
	{
		return in_array($this->type, ["rss2.0", "atom"]);
	}
	public function getLastUpdate()
	{
		if($this->type == "rss2.0")
		{
			return $this->root->lastBuildDate;
		}
		else if($this->type == "atom")
		{
			return $this->root->updated;
		}
		return "";
	}
	public function getTitle()
	{
		if($this->type == "rss2.0" || $this->type == "atom")
		{
			return $this->root->title;
		}
		return "";
	}
	public function getDescription()
	{
		if($this->type == "rss2.0")
		{
			return $this->root->description;
		}
		else if($this->type == "atom")
		{
			return $this->root->subtitle;
		}
		return "";
	}
	public function getWebsite()
	{
		if($this->type == "rss2.0")
		{
			return $this->root->link;
		}
		else if($this->type == "atom")
		{
			foreach($this->root->link as $link)
			{
				if($link["rel"] == "alternative")
				{
					return $link["href"];
				}
			}
		}
		return "";
	}
	public function getArticles()
	{
		$articles = [];
		if($this->type == "rss2.0")
		{
			foreach($this->root->item as $item)
			{
				array_push($articles, new Article($item, "rss2.0"));
			}
		}
		else if($this->type == "atom")
		{
			foreach($this->root->entry as $entry)
			{
				array_push($articles, new Article($entry, "atom"));
			}
		}
		return $articles;
	}
}
class Article
{
	public $element;
	private $type;
	public function __construct($element, $type)
	{
		$this->element = $element;
		$this->type = $type;
	}
	public function getName()
	{
		if($this->type == "rss2.0" || $this->type == "atom")
		{
			return $this->element->title;
		}
		return "";
	}
	public function getDescription()
	{
		if($this->type == "rss2.0")
		{
			return $this->element->description;
		}
		else if($this->type == "atom")
		{
			if($this->element->content)
			{
				return $this->element->content;
			}
			else if($this->element->summary)
			{
				return $this->element->summary;
			}
		}
		return "";
	}
	public function getPublished()
	{
		if($this->type == "rss2.0")
		{
			if($this->element->pubDate)
			{
				return strtotime($this->element->pubDate);
			}
		}
		else if($this->type == "atom")
		{
			if($this->element->published)
			{
				return strtotime($this->element->published);
			}
			else if($this->element->updated)
			{
				return strtotime($this->element->updated);
			}
		}
		return 0;
	}
	public function getCategories()
	{
		$categories = [];
		if($this->type == "rss2.0")
		{
			foreach($this->element->category as $category)
			{
				array_push($categories, $category);
			}
		}
		else if($this->type == "atom")
		{
			foreach($this->element->category as $category)
			{
				if($category["label"])
				{
					array_push($categories, $category["label"]);
				}
				else if($category["term"])
				{
					array_push($categories, $category["term"]);
				}
			}
		}
		return $categories;
	}
	public function getLink()
	{
		if($this->type == "rss2.0")
		{
			return $this->element->link;
		}
		else if($this->type == "atom")
		{
			return $this->element->link["href"];
		}
		return "";
	}
	public function getThumbnail()
	{
		$thumbnail = "";
		if($this->type == "rss2.0")
		{
			if($media = $this->element->children("media", true))
			{
				if($media->thumbnail)
				{
					$thumbnail = (string) $media->thumbnail->attributes()["url"];
				}
				else if($media->content)
				{
					$thumbnail = (string) $media->content->attributes()["url"];
				}
			}
		}
		return $thumbnail;
	}
}
