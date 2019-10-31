<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\FieldType\DBField;

class ImageSize extends DataObject
{

    private static $db = array(
        'GroupName' => 'Varchar',
        'PixelDensity' => "Enum('1.0,2.0','1.0')",
        'MinOrMax' => "Enum('min-width,max-width')",
        'DisplayWidth' => 'Int',
        'ImageWidth' => 'Int',
        'ImageHeight' => 'Int'
    );

    private static $summary_fields = array(
        'Rule' => 'Rule'
    );

    private static $casting = array(
        'Rule' => 'Varchar',
    );

    private static $table_name = 'ImageSize';

    public function getRule()
    {
        return DBField::create_field('Varchar', $this->GroupName . ': On a ' . $this->PixelDensity . 'x display, when the device is at a ' . $this->MinOrMax . ' of ' . $this->DisplayWidth .'px, the image is expected to be ' . $this->ImageWidth .'px wide by ' . $this->ImageHeight . 'px tall.');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('PixelDensity');
        $fields->removeByName('MinOrMax');
        $fields->removeByName('DisplayWidth');
        $fields->removeByName('ImageWidth');
        $fields->removeByName('ImageHeight');

        $fields->addFieldsToTab('Root.Main', array(

            TextField::create('GroupName', 'Group name')->setRightTitle('Add a group name'),
            HeaderField::create('heading', 'Display conditions', 4),
            FieldGroup::create(
                DropdownField::create('PixelDensity', 'Pixel Density', $this->dbObject('PixelDensity')->enumValues())->setRightTitle('Screen resolution. Most newer devices will have 2.0x displays.'),
                DropdownField::create('MinOrMax', 'Min or max width', $this->dbObject('MinOrMax')->enumValues()),
                TextField::create('DisplayWidth', 'Display Width')->setRightTitle('In pixels (px)')
            ),
            HeaderField::create('heading2', 'Image dimensions', 4),
            FieldGroup::create(
                TextField::create('ImageWidth', 'Image Width')->setRightTitle('In pixels (px)'),
                TextField::create('ImageHeight', 'Image Height')->setRightTitle('In pixels (px)')
            )
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
