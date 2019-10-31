<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\View\Requirements;
use UncleCheese\DisplayLogic\Forms\Wrapper as DisplayLogicWrapper;

/**
 * Type: DataExtension
 * Provides an Image Definition set capable of showing
 * multiple images as a slider. The main use of this
 * being a hero image. Has copied code from
 * {@link: ImageDefinitionSet} which needs
 * to be refactored
 *
 * @package ImageDefinition
 * @author andy.dover@dna.co.nz
 */

class ImageDefinitionMultiSet extends DataExtension
{

    private static $db = [
        'ImageTitle1' => 'Varchar(255)',
        'ImageTitle2' => 'Varchar(255)',
        'ImageTitle3' => 'Varchar(255)',
        'Add2' => 'Boolean',
        'Add3' => 'Boolean'
    ];

    private static $many_many = [
        'ImageDefinitions1' => ImageDefinition::class,
        'ImageDefinitions2' => ImageDefinition::class,
        'ImageDefinitions3' => ImageDefinition::class
    ];

    private static $casting = [
        'DefinedImages' => 'HTMLFragment'
    ];

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

    public function DefinedImages($enableSlider = true)
    {
        $def1 = $this->buildImageArray($this->owner->ImageDefinitions1());

        $images = new ArrayList();
        $def1 ? $images->add($def1) : null;

        if ($this->owner->stat('allow_slider') && $enableSlider !== 'false') {
            $def2 = $this->buildImageArray($this->owner->ImageDefinitions2());
            $def3 = $this->buildImageArray($this->owner->ImageDefinitions3());
            $def2 ? $images->add($def2) : null;
            $def3 ? $images->add($def3) : null;
        }

        return !empty($images) ? trim($this->owner->customise(new ArrayData([
            'HeroImages' => $images
        ]))->renderWith('ImageDefMulti')) : false;
    }

    public function getImageGridfields($max = 3)
    {
        $fields = [];

        $imageDefFields = singleton(ImageDefinition::class)->getCMSFields();

        $config = GridFieldConfig_RelationEditor::create();
        $config->getComponentByType(GridFieldDetailForm::class)->setFields($imageDefFields);
        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);

        if ($this->owner->SortOrder) {
            $config->addComponent($order = new GridFieldOrderableRows('SortOrder'));
        }

        for ($i = 0; $i < $max; $i++) {
            $TextFieldName = 'ImageTitle' . ($i + 1);
            $ImageFieldName = 'ImageDefinitions' . ($i + 1);
            $AddName = 'Add' . ($i + 2);
            $checkbox = ($i < $max - 1 && $this->owner->stat('allow_slider')) ? CheckboxField::create(
                $AddName,
                'Add another image?'
            ) : null;

            array_push($fields, DisplayLogicWrapper::create(
                HeaderField::create('H' . ($i + 1), 'Hero Image ' . ($i + 1), 4),
                TextField::create($TextFieldName, 'Image Title')->setRightTitle('This doubles up as the caption'),
                GridField::create(
                    $ImageFieldName,
                    'Hero Image Definitions',
                    call_user_func_array(array($this->owner, 'ImageDefinitions' . ($i + 1)), array()),
                    $config
                ),
                $checkbox
            ));

            // If it's not the first item we need to add some display logic
            // to only show if the checkbox is clicked
            if ($i > 0) {
                $fields[$i]->displayIf('Add' . ($i + 1))->isChecked();
            }
        }

        return $fields;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeFieldFromTab('Root', 'ImageTitle');
        $fields->removeFieldFromTab('Root', 'ImageTitle1');
        $fields->removeFieldFromTab('Root', 'ImageTitle2');
        $fields->removeFieldFromTab('Root', 'ImageTitle3');
        $fields->removeFieldFromTab('Root', 'ImageDefinitions1');
        $fields->removeFieldFromTab('Root', 'ImageDefinitions2');
        $fields->removeFieldFromTab('Root', 'ImageDefinitions3');
        $fields->removeFieldFromTab('Root', 'Add2');
        $fields->removeFieldFromTab('Root', 'Add3');

        if ($this->owner->isInDB() && $this->owner->stat('show_image_definitions')) {
            $fields->addFieldsToTab('Root.HeroImages', array_merge(array(
                HeaderField::create('ImageDefHeader', 'Hero Image Definitions', 4),
                LiteralField::create(
                    'info',
                    '<p class="message">Add one or more images here. Adding extra fields (max 3) will turn the hero into a slider. <br/>Multiple images need to be defined to display nicely at different resolutions.<br/><strong>Note: </strong>due to the way SilverStripe saves, you will need to save the page once you\'ve added all of the image titles, or else they will be removed.</p>'
                ),
            ), $this->getImageGridfields()));
        }

        return $fields;
    }
}
