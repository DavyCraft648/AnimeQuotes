<?php
declare(strict_types=1);

namespace DavyCraft648\AnimeQuotes\utils;

use DavyCraft648\AnimeQuotes\Main;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Attribute;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use vezdehod\packs\PluginContent;
use vezdehod\packs\ui\jsonui\binding\Binding;
use vezdehod\packs\ui\jsonui\binding\BindingType;
use vezdehod\packs\ui\jsonui\binding\DataBinding;
use vezdehod\packs\ui\jsonui\element\ImageElement;
use vezdehod\packs\ui\jsonui\element\StackPanelElement;
use vezdehod\packs\ui\jsonui\element\types\Anchor;
use vezdehod\packs\ui\jsonui\element\types\FontType;
use vezdehod\packs\ui\jsonui\element\types\Offset;
use vezdehod\packs\ui\jsonui\element\types\Orientation;
use vezdehod\packs\ui\jsonui\element\types\Size;
use vezdehod\packs\ui\jsonui\element\types\TextAlignment;
use vezdehod\packs\ui\jsonui\expression\Expression;
use vezdehod\packs\ui\jsonui\vanilla\form\SimpleFormDecorator;
use vezdehod\packs\ui\jsonui\vanilla\form\SimpleFormStyle;
use vezdehod\packs\ui\jsonui\variable\Variable;

final class FormUtils{

	public const ANIME_QUOTES_FORM = "§a§n§i§m§e§q§u§o§t§e§s§0§0§0";
	public const CHARACTER_IMAGE = "image";
	public const QUOTES_INFO = "§q§i§r";
	public const QUOTES_INFO_DATA = "§q§i§d§r";
	public const QUOTES_TEXT = "§q§t§r§f";

	public static SimpleFormDecorator $decorator;

	public static function init(PluginContent $content) : void{
		$style = $content->getUI()->getJsonUIs()->createSimpleFromStyle();
		$root = $style->addPanel($style->getRootName(), new Size("100% - 50px", "100% - 20px"));

		$form = $style->addExtends("form", "server_form.long_form");
		$form->size(new Size("100%", "100%"));
		$form->setVar('text_name', "Anime Quotes");

		$form_panel = $style->addExtends("form_panel", "server_form.long_form_panel");
		$form_panel->size(new Size("100%", "100%"));
		$form_panel->anchor(Anchor::CENTER);

		$scrolling_content = $style->addStackPanel("scrolling_content", Orientation::VERTICAL);
		$scrolling_content->size(new Size("100%", "100%c"));
		$scrolling_content->anchor(Anchor::CENTER);
		$quotes_image_info_panel = $scrolling_content->addStackPanel("quotes_image_info_panel", Orientation::HORIZONTAL);
		$quotes_image_info_panel->size(new Size("100%", "90px"));

		$image_panel_content = $style->addStackPanel("image_panel_content", Orientation::VERTICAL);
		$image_panel_content->size(new Size("100%", "100%"));
		$image = $image_panel_content->addImage("image");
		$image->size(new Size("100%", "100%"));
		$image->binding(DataBinding::overrideFromCollection(SimpleFormStyle::getFormButtons(), SimpleFormStyle::getButtonTextureBinding(), ImageElement::getTextureBinding()));
		$image->binding(DataBinding::overrideFromCollection(SimpleFormStyle::getFormButtons(), SimpleFormStyle::getButtonTextureFSBinding(), ImageElement::getTextureFSBinding()));
		$image->binding(DataBinding::viewRebind(new Expression("(not (#texture = ''))"), Binding::visible()));

		$image_panel = $quotes_image_info_panel->addStackPanel("image_panel", Orientation::VERTICAL);
		$image_panel->size(new Size("70px", "70px"));
		$image_panel->factory("buttons", $image_panel_content->getId());
		$image_panel->collectionName("form_buttons");
		$image_panel->binding(DataBinding::override(SimpleFormStyle::getButtonsLengthBinding(), StackPanelElement::getCollectionLengthBinding()));

		$quotes_info_binding = new DataBinding();
		$quotes_info_binding->binding_type = BindingType::COLLECTION;
		$quotes_info_binding->binding_collection_name = "form_buttons";
		$quotes_info_binding->binding_name = "(not ((#form_button_text - '" . self::QUOTES_INFO . "') = #form_button_text))";
		$quotes_info_binding->binding_name_override = "#visible";
		$quotes_info_binding->binding_condition = "once";

		$quotes_info_panel_content = $style->addStackPanel("quotes_info_panel_content", Orientation::VERTICAL);
		$quotes_info_panel_content->size(new Size("100%", "100%c - 5px"));
		$quotes_info_panel_content->binding($quotes_info_binding);
		$quotes_info = $quotes_info_panel_content->addExtends("quotes_info", "settings_common.option_group_label");
		$quotes_info->setVar("text", SimpleFormStyle::getButtonTextBinding());
		$quotes_info->setVar("text_bindings", [
			[
				"binding_name" => SimpleFormStyle::getButtonTextBinding(),
				"binding_type" => BindingType::COLLECTION,
				"binding_collection_name" => SimpleFormStyle::getFormButtons()
			]
		]);

		$quotes_info_panel = $quotes_image_info_panel->addStackPanel("quotes_info_panel", Orientation::VERTICAL);
		$quotes_info_panel->anchor(Anchor::TOP_LEFT);
		$quotes_info_panel->size(new Size("80px", "100%c"));
		$quotes_info_panel->factory("buttons", $quotes_info_panel_content->getId());
		$quotes_info_panel->collectionName("form_buttons");
		$quotes_info_panel->binding(DataBinding::override(SimpleFormStyle::getButtonsLengthBinding(), StackPanelElement::getCollectionLengthBinding()));

		$quotes_info_data_binding = new DataBinding();
		$quotes_info_data_binding->binding_type = BindingType::COLLECTION;
		$quotes_info_data_binding->binding_collection_name = "form_buttons";
		$quotes_info_data_binding->binding_name = "(not ((#form_button_text - '" . self::QUOTES_INFO_DATA . "') = #form_button_text))";
		$quotes_info_data_binding->binding_name_override = "#visible";
		$quotes_info_data_binding->binding_condition = "once";

		$quotes_info_data_panel_content = $style->addStackPanel("quotes_info_data_panel_content", Orientation::VERTICAL);
		$quotes_info_data_panel_content->size(new Size("100%", "100%c - 5px"));
		$quotes_info_data_panel_content->binding($quotes_info_data_binding);
		$quotes_info_data = $quotes_info_data_panel_content->addExtends("quotes_info_data", "settings_common.option_group_label");
		$quotes_info_data->setVar("text", SimpleFormStyle::getButtonTextBinding());
		$quotes_info_data->setVar("text_bindings", [
			[
				"binding_name" => SimpleFormStyle::getButtonTextBinding(),
				"binding_type" => BindingType::COLLECTION,
				"binding_collection_name" => SimpleFormStyle::getFormButtons()
			]
		]);

		$quotes_info_data_panel = $quotes_image_info_panel->addStackPanel("quotes_info_data_panel", Orientation::VERTICAL);
		$quotes_info_data_panel->anchor(Anchor::TOP_LEFT);
		$quotes_info_data_panel->size(new Size("100%", "100%c"));
		$quotes_info_data_panel->factory("buttons", $quotes_info_data_panel_content->getId());
		$quotes_info_data_panel->collectionName("form_buttons");
		$quotes_info_data_panel->binding(DataBinding::override(SimpleFormStyle::getButtonsLengthBinding(), StackPanelElement::getCollectionLengthBinding()));

		$quotes_text_binding = new DataBinding();
		$quotes_text_binding->binding_type = BindingType::COLLECTION;
		$quotes_text_binding->binding_collection_name = "form_buttons";
		$quotes_text_binding->binding_name = "(not ((#form_button_text - '" . self::QUOTES_TEXT . "') = #form_button_text))";
		$quotes_text_binding->binding_name_override = "#visible";
		$quotes_text_binding->binding_condition = "once";

		$quotes_text_panel_content = $style->addStackPanel("quotes_text_panel_content", Orientation::VERTICAL);
		$quotes_text_panel_content->anchor(Anchor::CENTER);
		$quotes_text_panel_content->size(new Size("100%", "100%c"));
		$quotes_text_panel_content->binding($quotes_text_binding);
		$quotes_text = $quotes_text_panel_content->addLabel("quotes_text");
		$quotes_text->text(SimpleFormStyle::getButtonTextBinding());
		$quotes_text->anchor(Anchor::CENTER);
		$quotes_text->textAlignment(TextAlignment::CENTER);
		$quotes_text->maxSize(new Size("100%", "default"));
		$quotes_text->offset(new Offset("0px", "4px"));
		$quotes_text->lockedAlpha(0.5);
		$quotes_text_label_binding = new DataBinding();
		$quotes_text_label_binding->binding_name = SimpleFormStyle::getButtonTextBinding();
		$quotes_text_label_binding->binding_type = BindingType::COLLECTION;
		$quotes_text_label_binding->binding_collection_name = SimpleFormStyle::getFormButtons();
		$quotes_text->binding($quotes_text_label_binding);
		$quotes_text->fontScaleFactor(1.5);
		$quotes_text->fontType(FontType::DEFAULT);
		$quotes_text->color(new Variable("main_header_text_color"));

		$quotes_text_panel = $scrolling_content->addStackPanel("quotes_text_panel", Orientation::VERTICAL);
		$quotes_text_panel->anchor(Anchor::CENTER);
		$quotes_text_panel->size(new Size("100%", "100%c"));
		$quotes_text_panel->factory("buttons", $quotes_text_panel_content->getId());
		$quotes_text_panel->collectionName("form_buttons");
		$quotes_text_panel->binding(DataBinding::override(SimpleFormStyle::getButtonsLengthBinding(), StackPanelElement::getCollectionLengthBinding()));

		$scrolling_panel = $form_panel->addExtends("scrolling_panel", "common.scrolling_panel");
		$scrolling_panel->anchor(Anchor::CENTER);
		$scrolling_panel->setVar('show_background', false);
		$scrolling_panel->size(new Size("100%", "100%"));
		$scrolling_panel->setVar('scrolling_content', $scrolling_content->getId());
		$scrolling_panel->setVar('scroll_size', [2, "100% - 4px"]);
		$scrolling_panel->setVar('scrolling_pane_size', ["100% - 4px", "100% - 2px"]);
		$scrolling_panel->setVar('scrolling_pane_offset', [4, 0]);
		$scrolling_panel->setVar('scroll_bar_right_padding_size', [0, 0]);
		$scrolling_panel->setVar('allow_scrolling_even_when_content_fits', false);

		$form->setVar('child_control', $form_panel->getId());

		$root->addExtends("form", $form->getId());

		$style->setRootElement($root);

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		self::$decorator = $style->getDecorator();
	}

	public static function sendForm(
		Player $player,
		string $imageUrl,
		string $char,
		string $anime,
		string $episode,
		string $date,
		string $quotes
	) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) : void{
		});
		$form->setTitle(self::ANIME_QUOTES_FORM);
		$form->addButton(self::CHARACTER_IMAGE, SimpleForm::IMAGE_TYPE_URL, $imageUrl);
		$contents = [
			"character-name" => $char,
			"anime-name" => $anime,
			"episode" => $episode,
			"quotes-date" => $date
		];
		foreach(QuotesUtils::$contents as $i => $name){
			$form->addButton(self::QUOTES_INFO . $name);
			$form->addButton(self::QUOTES_INFO_DATA . ": $contents[$i]");
		}
		$form->addButton(self::QUOTES_TEXT . $quotes);
		//$player->sendForm(self::$decorator->decorate($form));
		$player->sendForm($form);

		Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player) : void{
			$player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL)->markSynchronized(false);
		}), 20);
	}
}
