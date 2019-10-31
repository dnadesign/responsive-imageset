<?php

namespace DNADesign\ResponsiveImageSet;

use SilverStripe\Admin\ModelAdmin;

class ImageSizeAdmin extends ModelAdmin
{

    private static $managed_models = array(
        ImageSize::class
    );

    private static $menu_title = 'Image Sizes';

    private static $url_segment = 'imagesizes';

    private static $menu_icon = 'dnadesign/silverstripe-responsiveimageset:client/images/icon.png';

}
