<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\MVC\Model\FormBehaviorTrait;

defined('_JEXEC') or die;

/**
 * Digiwallet_donation form
 */
class FormDigiwallet_donation_buttonDigiwallet_donation
{
    use FormBehaviorTrait;
    use FormFactoryAwareTrait;

    /**
     * The items
     */
    protected $items = [];

    /**
     * The keys for the field options
     *
     * @var array
     */
    protected $fieldOptionKeys = [];

    /**
     * Method to get the record form.
     *
     * @param    array $data An optional array of data for the form to interogate
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not
     * @param null $source
     *
     * @return bool A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true, $source = NULL)
    {
        // Get the form
        $form = $this->loadForm('com_digiwallet_donation.digiwallet_donation_button', 'digiwallet_donation_button', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    /**
     * Get the field options from the form fields
     *
     * @return  array   $fieldOptions   An array with the field options
     */
    public function getFieldOptions()
    {
        $form = $this->getForm();

        $xmlFieldset = $form->getXml()->fieldset;

        $fieldOptions = array();
        foreach ($xmlFieldset->children() as $field)
        {
            $fieldColumn = (string) $field['name'];

            foreach ($field->children() as $option)
            {
                $key = (string) $option['value'];
                $value = (string) $option;

                if (!in_array($key, $this->fieldOptionKeys, true))
                {
                    $this->fieldOptionKeys[] = $key;
                }

                $fieldOptions[$fieldColumn][$key] = $value;
            }
        }

        return $fieldOptions;
    }

    /**
     * Append options from the form to the items
     *
     * @param $items
     *
     * @return array
     */
    public function appendFieldOptions($items)
    {
        $this->items = $items;

        $fieldOptions = $this->getFieldOptions();

        foreach ($this->items as $i => $item)
        {
            foreach ($item as $key => $value)
            {
                if ((string)$key === 'state')
                {
                    continue;
                }

                if (!in_array($item->{$key}, $this->fieldOptionKeys, true))
                {
                    continue;
                }

                // If this field has options
                if (isset($fieldOptions[$key]))
                {
                    // Update the item key with the field option
                    $item->{$key} = JText::_($fieldOptions[$key][$value]);
                }
            }

            $this->items[$i] = $item;
        }

        return $this;
    }

    /**
     * Get one item
     *
     * @return null
     */
    public function getOne()
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get all the items
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->items;
    }
}
