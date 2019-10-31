<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\CheckboxField;
use UncleCheese\DisplayLogic\Forms\Wrapper as DisplayLogicWrapper;
/**
 * Provides an Image Definition set capable of showing one image. Has duplicate
 * code with {@link: ImageDefinitionMultieSet} which needs to be refactored
 */

class ImageDefinitionSet extends DataExtension
{

    private static $db = array(
        'ImageTitle' => 'Varchar(255)',
        'AdditionalDescription' => 'Varchar(255)'
    );

    private static $many_many = array(
        'ImageDefinitions' => ImageDefinition::class
    );

    private static $many_many_extraFields = array(
        'ImageDefinitions' => array(
            'SortOrder' => 'Int'
        )
    );

    private static $show_image_definitions = true;


    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.AdditionalDisplayItems', array_merge(array(
            HeaderField::create('ImageDefHeader', 'Additional Display Items', 4),
            LiteralField::create(
                'info',
                '<p class="message">This area is where you can define extra display items which could appear on other sections of the site but are directly related to the current page.</p>'
            ),
            TextField::create('Tagline', 'Tag line')->setRightTitle('A tag line for the product'),
            TextareaField::create(
                'AdditionalDescription',
                'Additional Description'
            )->setRightTitle('An additional description for the product')
        ), $this->getImageGridfields()));
    }

    public function buildImageArray($imageDefs)
    {
        $arrayList = new ArrayList();

        Requirements::javascript('dnadesign/silverstripe-responsiveimageset:client/javascript/picturefill/picturefill.min.js');

        foreach ($imageDefs as $i) {
            $str = '(' . $i->ImageSize()->MinOrMax . ': ' . $i->ImageSize()->DisplayWidth . 'px)';

            if (intval($i->ImageSize()->PixelDensity) > 1) {
                $str .= ' and (min-device-pixel-ratio: ' . $i->ImageSize()->PixelDensity . ')';
            }

            $arrayList->push(array(
                'DisplayWidth' => $i->ImageSize()->DisplayWidth, // Only added for sorting reasons
                'String' => $str,
                'Image' => $i->DefinedImage()
            ));
        }

        // Sort by highest number first
        $arrayList = $arrayList->sort('DisplayWidth')->reverse();

        if ($arrayList->Count() > 0) {
            return array('Images' => $arrayList);
        }

        return false;
    }

    public function getDefinedAdditionalImage()
    {
        $a = $this->buildImageArray($this->owner->ImageDefinitions());

        if ($a) {
            return $this->owner->customise(new ArrayData($a))->renderWith('ImageDef');
        } else {
        }
    }

    public function getImageGridfields($max = 3)
    {
        $fields = [];

        $imageDefFields = singleton(ImageDefinition::class)->getCMSFields();

        $config = GridFieldConfig_RelationEditor::create();
        $config->getComponentByType(GridFieldDetailForm::class)->setFields($imageDefFields);
        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);

        array_push($fields, DisplayLogicWrapper::create(
            HeaderField::create('H', 'Additional Image', 4),
            TextField::create('ImageTitle', 'Image Title')->setRightTitle('This doubles up as the caption'),
            GridField::create(
                'ImageDefinitions',
                'Additional Image Definitions',
                call_user_func_array(array($this->owner, 'ImageDefinitions'), array()),
                $config
            )
        ));

        return $fields;
    }
}
