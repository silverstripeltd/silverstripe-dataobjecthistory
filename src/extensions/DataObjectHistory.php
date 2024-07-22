<?php

namespace gorriecoe\DataObjectHistory\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldViewButton;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;
use gorriecoe\DataObjectHistory\Forms\GridFieldHistoryButton;
use gorriecoe\DataObjectHistory\Forms\HistoryGridFieldItemRequest;

/**
 * DataObjectHistory
 *
 * @package silverstripe-dataobjecthistory
 */
class DataObjectHistory extends DataExtension
{
    /**
     * @inheritDoc
     */
    public function updateCMSFields(FieldList $fields): FieldList
    {
        $owner = $this->owner;
        if ($owner->HistoryFields) {
            $fields->addFieldsToTab(
                'Root.History',
                $owner->HistoryFields
            );
        }

        return $fields;
    }

    /**
     * Returns the history fields for this DataObject.
     *
     * @return FieldList|null
     */
    public function getHistoryFields(): ?FieldList
    {
        $owner = $this->owner;
        if (!$owner->isLatestVersion()) {
            return null;
        }

        $config = GridFieldConfig_RecordViewer::create()
            ->removeComponentsByType([
                GridFieldToolbarHeader::class,
                GridFieldSortableHeader::class,
                GridFieldPaginator::class,
                GridFieldPageCount::class,
                GridFieldViewButton::class
            ])
            ->addComponent(new GridFieldTitleHeader)
            ->addComponent(new GridFieldHistoryButton);
        $config->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(HistoryGridFieldItemRequest::class);
        $config->getComponentByType(GridFieldDataColumns::class)
            ->setDisplayFields([
                'Version' => '#',
                'LastEdited.Nice' => _t(__CLASS__ . '.WHEN', 'When'),
                'Title' => _t(__CLASS__ . '.TITLE', 'Title'),
                'Author.Name' => _t(__CLASS__ . '.AUTHOR', 'Author')
            ]);

        return FieldList::create(
            GridField::create(
                'History',
                '',
                Versioned::get_all_versions(
                    $owner->ClassName,
                    $owner->ID
                )->sort('Version', 'DESC'),
                $config
            )->addExtraClass('grid-field--history')
        );
    }

    /**
     * @return Member|null
     */
    public function getAuthor(): ?Member
    {
        $owner = $this->owner;
        if ($owner->AuthorID) {
            return Member::get()->byId($owner->AuthorID);
        }

        return null;
    }
}
