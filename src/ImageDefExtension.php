<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Provides CMS Administration of {@link: ImageDefinition} objects
 *
 * @package ImageDefinition
 * @author andy.dover@dna.co.nz
 */

class ImageDefExtension extends DataExtension
{

    private static $db = array(
        'ImageTitle' => 'Varchar(255)'
    );

    private static $many_many = array(
        'ImageDefinitions' => ImageDefinition::class
    );

    private static $summary_fields = array(
        'ImageTitle' => 'Image Title'
    );

    private static $many_many_extraFields = array(
        'ImageDefinitions' => array(
            'SortOrder' => 'Int'
        )
    );

    private static $casting = [
        'getDefinedImages' => 'HTMLFragment',
        'DefinedImages' => 'HTMLFragment'
    ];

    private static $show_image_definitions = true;

    public function getDefinedImages()
    {
        $arrayList = new ArrayList();

        foreach ($this->owner->ImageDefinitions() as $i) {
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

        return $this->owner->customise(new ArrayData(array(
            'Images' => $arrayList
        )))->renderWith('ImageDef');
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeFieldFromTab('Root', 'ImageDefinitions');

        if ($this->owner->isInDB() && $this->owner->stat('show_image_definitions')) {
            $imageDefFields = singleton(ImageDefinition::class)->getCMSFields();

            $config = GridFieldConfig_RelationEditor::create();
            $config->getComponentByType(GridFieldDetailForm::class)->setFields($imageDefFields);
            $config->removeComponentsByType($config->getComponentByType(GridFieldAddExistingAutocompleter::class));

            if ($this->owner->SortOrder) {
                $config->addComponent($order = new GridFieldOrderableRows('SortOrder'));
            }

            $gridfield = GridField::create('ImageDefinitions', 'Hero Image Definitions', $this->owner->ImageDefinitions(), $config);
            $fields->addFieldsToTab('Root.Main', array(
                HeaderField::create('ImageDefHeader', 'Hero Image Definitions', 4),
                LiteralField::create('warn', '<p class="message notice">For the main hero image, multiple images need to be defined to display nicely at different resolutions.</p>'),
                TextField::create('ImageTitle', 'Image Title'),
                $gridfield
            ));
        }

        return $fields;
    }
}
