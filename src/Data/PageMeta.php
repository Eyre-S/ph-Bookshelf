<?php

namespace SukWs\Bookshelf\Data;

use Exception;
use SukWs\Bookshelf\Data\SiteConfig\ConfigName;
use SukWs\Bookshelf\Element\BookContent\BookContented;
use SukWs\Bookshelf\Element\BookContent\Page;
use SukWs\Bookshelf\Utils\Markdown\Markdown;
use SukWs\Bookshelf\Utils\RequestNotExistException;

class PageMeta {
	
	public static string $bookId;
	public static BookContented $book;
	public static string $page_id;
	public static PageData $page_data;
	public static bool $isMainPage = false;
	
	/**
	 * @throws RequestNotExistException
	 * @throws Exception
	 */
	public static function init (array $uri): bool {
		
		if (!isset($uri[0]) || $uri[0] == "%root" || $uri[0] == "/" || $uri[0] == "") {
			self::$book = SiteMeta::getBookshelf()->getRootBook();
			self::$bookId = "%root";
		} else {
			$tmp = SiteMeta::getBookshelf()->getBook($uri[0]);
			if ($tmp == null)
				throw new RequestNotExistException("Book required \"$uri[0]\" not found!");
			self::$bookId = $uri[0];
			self::$book = $tmp->getContentedNode();
		}
		
		if (isset($uri[1])) {
			self::$page_id = $uri[1];
		} else {
			$tmp = self::$book->getChildren()->getChildren()[0];
			if ($tmp instanceof Page) self::$page_id = $tmp->getId();
			else throw new RequestNotExistException("No main page in required book \"$uri[0]\"");
			self::$isMainPage = true;
		}
		if ($content = @file_get_contents(self::getPagePath("md"))) {
			self::$page_data = (new Markdown())->parse($content);
		} else {
			return false;
		}
		
		return true;
		
	}
	
	public static function getHtmlTitle (): string {
		return self::getPageTitle() . " - " . self::$book->getName();
	}
	
	public static function getPageTitle (): string {
		if (self::$page_data->title === null) {
			$page_def = self::$book->getPage(self::$page_id);
			if ($page_def === null) {
				return "...";
			}
			return $page_def->getName();
		}
		return self::$page_data->title;
	}
	
	public static function getDescription (): string {
		return ""; // todo wip description
	}
	
	public static function getConfigurationLevelBook (string $key): ?string {
		$value = SiteMeta::getConfigurationLevelShelf($key);
		$valueAttr = self::$book->getConfiguration($key);
		if ($valueAttr != null) $value = $valueAttr;
		return $value;
	}
	
	public static function getConfigurationLevelPage (string $key): ?string {
		$value = self::getConfigurationLevelBook($key);
		$valueAttr = self::$page_data->getConfiguration($key);
		if ($valueAttr != null) $value = $valueAttr;
		return $value;
	}
	
	public static function highlightJsTheme (): string {
		$theme = trim(self::getConfigurationLevelPage(ConfigName::highlightjs_theme));
		if (empty($theme)) return "atom-one-dark";
		return $theme;
	}
	
	/**
	 * @return string[]
	 */
	public static function highlightJsLanguages (): array {
		$langDef = "";
		{
			$langDefList = array();
			$langDefList[] = SiteMeta::getConfigurationLevelShelf(ConfigName::highlightjs_lang);
			$langDefList[] = PageMeta::getConfigurationLevelBook(ConfigName::highlightjs_lang);
			$langDefList[] = PageMeta::getConfigurationLevelPage(ConfigName::highlightjs_lang);
			foreach ($langDefList as $langDefNode) $langDef .= $langDefNode . ";";
		}
		$lang = array();
		foreach (explode(";", $langDef) as $i) {
			$i = trim($i);
			if ($i != "") $lang[] =$i;
		}
		$lang = array_unique($lang);
		return $lang;
	}
	
	public static function getPagePath (?string $extension = null): string {
		return "./data/" . self::$bookId . "/" . self::$page_id . ($extension == null ? "" : ".".$extension);
	}
	
}
