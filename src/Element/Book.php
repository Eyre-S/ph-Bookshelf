<?php

require_once "./src/Element/BookCollection.php";
require_once "./src/Element/BookContent/BookContented.php";

class Book {
	
	private string $id;
	private string $name;
	private BookCollection $parent;
	
	public function __construct (string $id, string $name, BookCollection $parent) {
		$this->id = $id;
		$this->name = $name;
		$this->parent = $parent;
	}
	
	/**
	 * @param DOMNode $xmlData
	 * @param BookCollection $parent
	 * @return Book
	 * @throws Exception
	 */
	public static function parse (DOMNode $xmlData, BookCollection $parent): Book {
		if ($xmlData->hasAttributes()) {
			$attrName = $xmlData->attributes->getNamedItem("name");
			$attrId = $xmlData->attributes->getNamedItem("id");
			if ($attrName == null)
				if ($attrId == null) throw new Exception("Book xml data missing attribute \"name\"");
				else throw new Exception("Book xml data with id \"$attrId->nodeValue\" missing attribute \"name\"");
			else $name = $attrName->nodeValue;
			if ($attrId == null) throw new Exception("Book xml data named \"$name\" missing attribute \"id\"");
			else $id = $attrId->nodeValue;
		} else
			throw new Exception("Book xml data missing attributes");
		if ($xmlData->hasChildNodes())
			throw new Exception("Book xml with id \"$id\" have some children which are not supported");
		return new Book($id, $name, $parent);
	}
	
	public function getId (): string {
		return $this->id;
	}
	
	public function getName (): string {
		return $this->name;
	}
	
	/**
	 * @return BookCollection|null
	 */
	public function getParent (): BookCollection {
		return $this->parent;
	}
	
	public function getHtml (): string {
		return "<li class='link" . (PageMeta::$book->getId()==$this->id?" active":"") . "'><a class='link' " . (PageMeta::$book->getId()==$this->id?"":" href='/$this->id'") . ">$this->name</a></li>";
	}
	
	/**
	 * @throws Exception
	 */
	public function getContentedNode (): BookContented {
		return BookContented::parseString(file_get_contents("./data/$this->id/book.xml"));
	}
	
}
