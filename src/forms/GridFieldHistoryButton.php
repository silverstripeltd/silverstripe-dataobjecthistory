<?php

namespace gorriecoe\DataObjectHistory\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use SilverStripe\Forms\GridField\GridFieldViewButton;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * DataObjectHistory
 *
 * @package silverstripe-dataobjecthistory
 */
class GridFieldHistoryButton extends GridFieldViewButton
{
    /*
     * @inheritDoc
     */
    public function getColumnContent($field, $record, $col)
    {
        if ($record->isLatestVersion()) {
            return null;
        }

        $data = new ArrayData(
            [
                'Link' => Controller::join_links(
                    $field->Link('item'),
                    $record->ID,
                    'view?VersionID=' . $record->Version
                )
            ]
        );

        $template = SSViewer::get_templates_by_class(
            $this,
            '',
            GridFieldViewButton::class
        );

        return $data->renderWith($template);
    }
}
