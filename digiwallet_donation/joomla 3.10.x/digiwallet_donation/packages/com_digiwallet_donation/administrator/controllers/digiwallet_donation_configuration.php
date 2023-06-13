<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class Digiwallet_donationControllerDigiwallet_donation_configuration extends JControllerForm
{

    public function save($key = null, $urlVar = null)
    {
        $app   = \JFactory::getApplication();
        $task = $this->getTask();

        jimport('joomla.filesystem.file');
        $jinput = JFactory::getApplication()->input;
        $data = [
            'dw_rtlo' => $this->input->post->get('dw_rtlo'),
            'dw_token' => $this->input->post->get('dw_token'),
            'dw_return' => $_POST['dw_return'],
            'digiwallet_enable_ide' => $this->input->post->get('digiwallet_enable_ide'),
            'digiwallet_enable_mrc' => $this->input->post->get('digiwallet_enable_mrc'),
            'digiwallet_enable_deb' => $this->input->post->get('digiwallet_enable_deb'),
            'digiwallet_enable_wal' => $this->input->post->get('digiwallet_enable_wal'),
            'digiwallet_enable_cc' => $this->input->post->get('digiwallet_enable_cc'),
            'digiwallet_enable_bw' => $this->input->post->get('digiwallet_enable_bw'),
            'digiwallet_enable_afp' => $this->input->post->get('digiwallet_enable_afp'),
            'digiwallet_enable_pyp' => $this->input->post->get('digiwallet_enable_pyp'),
            'digiwallet_enable_eps' => $this->input->post->get('digiwallet_enable_eps'),
            'digiwallet_enable_gip' => $this->input->post->get('digiwallet_enable_gip'),
        ];
        $model = $this->getModel('Configuration');
        $model->store($data);

        if ($task == 'apply')
        {
            $this->_redirect('index.php?option=com_digiwallet_donation&view=digiwallet_donation_configuration', JText::_('DIGIWALLET_DONATION_CONFIGURATION_DATA_HAVE_BEEN_SAVED_SUCCESSFULLY'));
        }
        else
        {
            $this->_redirect('index.php?option=com_digiwallet_donation&view=digiwallet_donation_buttons', JText::_('DIGIWALLET_DONATION_CONFIGURATION_DATA_HAVE_BEEN_SAVED_SUCCESSFULLY'));
        }
    }


    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_digiwallet_donation&view=digiwallet_donation_buttons');
    }

    public function _redirect($url, $message = null){
        $app = JFactory::getApplication();
        if ($message) {
            $app->enqueueMessage($message, 'message');
        }

        $app->redirect($url);
    }

    /**
     * Proxy for getModel
     * @since	1.6
     */
    public function getModel($name = 'digiwallet_donation_configuration', $prefix = 'Digiwallet_donationModel', $config = [])
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
} 