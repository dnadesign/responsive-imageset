<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\Security\Permission;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;

class ImageDefinition extends DataObject
{

    private static $summary_fields = array(
        'Rule' => 'Rule'
    );

    private static $has_one = array(
        'ImageSize' => ImageSize::class,
        'DefinedImage' => Image::class
    );

    private static $table_name = 'ImageDefinition';

    public function getRule()
    {
        return $this->ImageSize()->Rule;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root.Main', 'ImageSizeID');
        $fields->addFieldsToTab('Root.Main', array(

            UploadField::create('DefinedImage', 'Defined Image')
                ->setAllowedFileCategories('image')
                ->setAllowedMaxFileNumber(1)
                ->setFolderName('Uploads/images/responsiveImageDefinitions'),
            DropdownField::create('ImageSizeID', 'Image size this relates to', ImageSize::get()->map('ID', 'Rule')->toArray())->setEmptyString('Choose image size')->setRightTitle('Image size presets need to be created in the Image Sizes admin.')
        ));

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function canView($member = null) {
        return Permission::check('HEROIMAGE', 'any', $member);
    }

    public function canEdit($member = null) {
        return Permission::check('HEROIMAGE', 'any', $member);
    }

    public function canDelete($member = null) {
        return Permission::check('HEROIMAGE', 'any', $member);
    }

    public function canCreate($member = null, $context = []) {
        return Permission::check('HEROIMAGE', 'any', $member);
    }

}
