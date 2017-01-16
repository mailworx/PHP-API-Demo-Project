<pre><?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  This is a sample implementation for using the mailworx API in order to create and send an email campaign over mailworx.                    ---
---  Be aware of the fact that this example might not work in your mailworx account.                                                            ---
---																																				---
---  The following API methods get used in this example:                                                                                        ---
---     • GetProfiles                   http://www.mailworx.info/de/api/wunschsystem/beschreibung/getprofiles                                   ---
---     • GetSubscriberFields           http://www.mailworx.info/de/api/wunschsystem/beschreibung/getsubscriberfields                           ---
---     • ImportSubscribers             http://www.mailworx.info/de/api/wunschsystem/beschreibung/ImportSubscribers                             ---
---     • GetCampaigns                  http://www.mailworx.info/de/api/wunschsystem/beschreibung/GetCampaigns                                  ---
---     • CopyCampaign                  http://www.mailworx.info/de/api/wunschsystem/beschreibung/CopyCampaign                                  ---
---     • UpdateCampaign                http://www.mailworx.info/de/api/wunschsystem/beschreibung/UpdateCampaign                                ---
---     • GetSectionDefinitions         http://www.mailworx.info/de/api/wunschsystem/beschreibung/GetSectionDefinitions                         ---
---     • CreateSection                 http://www.mailworx.info/de/api/wunschsystem/beschreibung/CreateSection                                 ---
---     • SendCampaign                  http://www.mailworx.info/de/tour/e-mail/wunschsystem/beschreibung/versenden-einer-kampagne-sendcampaign ---
---                                                                                                                                             ---
---   This is a step by step example:                                                                                                           ---
---     1. Import the subscribers into mailworx                                                                                                 ---
---     2. Create a campaign                                                                                                                    ---
---     3. Add sections to the campaign                                                                                                         ---
---     4. Send the campaign to the imported subscribers                                                                                        ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/

include_once 'Classes\Importer.php';
include_once 'Classes\CampaignCreator.php';
include_once 'Classes\SectionCreator.php';
include_once 'Classes\Constants.php';
include_once 'mx_rest_api.php';

// Set  the login data.  
$securityContext = getSecurityContext();

// ### STEP 1 : IMPORT ###

// Here we use a helper class in order to do all the necessary import steps.
$importer = new \mailworx\Importer($securityContext);

// The key is the id of the profile where the subscribers have been imported to.
// The value is a list of ids of the imported subscribers.
$importedData = $importer->importSubscribers();

// ### STEP 1 : IMPORT ###
if (!is_null($importedData) && count($importedData['importedSubscribers']) > 0) {

    // ### STEP 2 : CREATE CAMPAIGN ###
    // Here we use another helper class in order to do all the necessary steps for creating a campaign.
    $campaignCreator = new \mailworx\CampaignCreator($securityContext);

    // The key is the id of the template.
			// The value is the id of the campaign.
    $data = $campaignCreator->createCampaign($importedData['profileId']);
  
    // ### STEP 2 : CREATE CAMPAIGN ###

    // If a campaign was returned we can add the sections.
    if (!is_null($data)) {
            // ### STEP 3 : ADD SECTIONS TO CAMPAIGN ###
            // Here we use another helper class in order to do all the necessary steps for adding sections to the campaign.
            $sectionCreator = new \mailworx\SectionCreator($securityContext);

            // Send the campaign, if all sections have been created.
            if ($sectionCreator->generateSection($data['templateId'], $data['campaignId'])) {
                 // ### STEP 3 : ADD SECTIONS TO CAMPAIGN ###

                 // ### STEP 4 : SEND CAMPAIGN ###
                 $sendCampaignRequest = new \mailworx\JSON(false);
                 $sendCampaignRequest->setCredentialsByObject($securityContext);
                 $sendCampaignRequest->setMethod('SendCampaign');
                 $sendCampaignRequest->setProperty('CampaignId', $data['campaignId']);
                 $sendCampaignRequest->setProperty('IgnoreCulture', false); // Send the campaign only to subscribers with the same language as the campaign
                 $sendCampaignRequest->setProperty('SendType', \mailworx\SendType::MANUAL);
                 $sendCampaignRequest->setProperty('Language', 'EN');
                 // If the SendType is set to Manual, ManualSendSettings are needed
                 // If the SendType is set to ABSplit, ABSplitTestSendSettings are needed
                 $sendCampaignRequest->setProperty('Settings', array(
                     '__type' => \mailworx\Constants::MANUAL_SEND_SETTINGS_TYPE,
                     'SendTime' => $sendCampaignRequest->getTime( date('Y-m-d H:i:s'))
                 ));
                 $sendCampaignRequest->setProperty('UseIRated', false); // Here is some more info about iRated http://www.mailworx.info/en/irated-technology
                 $sendCampaignRequest->setProperty('UseRTR', true);
                 $sendCampaignResponse = $sendCampaignRequest->getData();
                 // ### STEP 4 : SEND CAMPAIGN ###
                 if(is_null($sendCampaignResponse)){
                    echo 'Sth went wrong';
                 }
                 else{
                    echo 'Effective subscribers: '.$sendCampaignResponse->RecipientsEffective;
                }
        }
    }
}

function getSecurityContext()
{
    return  array(
        'Account' => 'eworx testaccount',   // The name of your mailworx account.
        'Username' => 'Webservice TestApp User', // Your mailworx username.
        'Password' => 'Passwort1', // Your mailworx password.
        'Source'  => 'mwTestApp'
         /* The name of your application which wants to access the mailworx webservice.
             You must register your application source at the following page, before you try to access the mailworx webservice: 
             http://www.mailworx.info/de/api/api-schnittstelle-erstellen*/
    );
}

?></pre>
<!DOCTYPE html>
<html>
<head>
    <style>
        html {
            font: 14px/1em sans-serif;
        }

        pre {
            border: 1px solid #ccc;
            padding: 10px;
            background: #eee;
        }

        .buttons a {
            display: inline-block;
            margin-right: 5px;
            background: silver;
            color: #fff;
            padding: 5px;
        }
    </style>
    <div>
        Hello World!
    </div>
</head>
