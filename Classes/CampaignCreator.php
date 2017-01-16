<?php
//This class will show you how a campaign can be created and updated in mailworx.

namespace mailworx;
include_once 'mx_rest_api.php';
class CampaignCreator
{
    private $securityContext;
    const CAMPAIGN_NAME = 'mailworx campaign';
        
    function __construct($securityContext){
        $this->securityContext = $securityContext;
    }

    /*
    /// Description: Creates a campaign in mailworx.
	/// Parameter profileId: The profile id that should be used for the campaign.
	/// Returns: KeyValuePair where the key is the template id and the value is the created campaign id.*/
    public function createCampaign($profileId){
        // Load the original campaign.
        $originalCampaign = $this->loadCampaign();
        $data = null;

        if (!is_null($originalCampaign)) {
            if ($originalCampaign->Name == CampaignCreator::CAMPAIGN_NAME) {

                // Copy the original campaign
                $copyCampaign = $this->copyCampaign($originalCampaign->Guid);

                // Update the sender, profile, ....
                if ($this->updateCampaign($copyCampaign, $profileId)) {
                    return array ('campaignId' => $copyCampaign->Guid,
                                  'templateId' => $copyCampaign->TemplateGuid);
                }
            } 
            else {
                // Return the already existing campaign.
                $data = array('campaignId' => $originalCampaign->Guid,
                              'templateId'=> $originalCampaign->TemplateGuid);
            }
        }

        return $data;
    }
        
    private function updateCampaign($campaignToUpdate, $profileId){
        // Every value of type string in the UpdateCampaignRequest must be assigned, otherwise it will be updated to the default value (which is string.Empty).

        $updateCampaignRequest = new \mailworx\JSON(false);
        $updateCampaignRequest->setCredentialsByObject($this->securityContext);
        $updateCampaignRequest->setMethod('UpdateCampaign');
        $updateCampaignRequest->setProperty('CampaignGuid', $campaignToUpdate->Guid);
        $updateCampaignRequest->setProperty('Language', 'EN');
        $updateCampaignRequest->setProperty('ProfileGuid', $profileId);
        $updateCampaignRequest->setProperty('Name', 'My first campaign');
        $updateCampaignRequest->setProperty('SenderAddress', 'service@mailworx.info');
        $updateCampaignRequest->setProperty('SenderName', 'mailworx Service Crew');
        $updateCampaignRequest->setProperty('Subject', 'My first Newsletter');

        return !is_null($updateCampaignRequest->getData());
    }

    private function copyCampaign($campaignId){
        $copyCampaignRequest = new \mailworx\JSON(false);
        $copyCampaignRequest->setCredentialsByObject($this->securityContext);
        $copyCampaignRequest->setMethod('CopyCampaign');
        $copyCampaignRequest->setProperty('Language', 'EN');
        $copyCampaignRequest->setProperty('CampaignToCopy', $campaignId); // The campaign which should be copied.

        $copyCampaignResponse =  $copyCampaignRequest->getData();

        if (is_null($copyCampaignResponse)) {
            return null;
        } 
        else {
            return $this->loadCampaign($copyCampaignResponse->NewCampaignGuid);
        }
    }

    private function loadCampaign($campaignId) {
        $campaignRequest = new \mailworx\JSON(false);
        $campaignRequest->setCredentialsByObject($this->securityContext);
        $campaignRequest->setMethod('GetCampaigns');
        $campaignRequest->setProperty('Type', \mailworx\CampaignType::IN_WORK);
        $campaignRequest->setProperty('Language', 'EN');

        if (is_null($campaignId)) { // If there is no campaign id given, then load the campaign by its name.
            $campaignsResponse = $campaignRequest->getData();
            $existingCampaign = null;
            foreach ($campaignsResponse->Campaigns as $campaign) {
                if (strcasecmp($campaign->Name, 'My first campaign') == 0) {
                    $existingCampaign = $campaign;
                    break;
                }
            }

            if (is_null($existingCampaign)) {
                foreach ($campaignsResponse->Campaigns as $tcampaign) {
                    if (strcasecmp($tcampaign->Name, CampaignCreator::CAMPAIGN_NAME ) == 0) {
                        $existingCampaign = $tcampaign;
                        break;
                    }
                }
            }
        } else { // If there is a campaign id given, then load the campaign by its id.
            $campaignRequest->setProperty('Id', $campaignId);
            $campaignsResponse = $campaignRequest->getData();
                
            if (is_null($campaignsResponse) || count($campaignsResponse->Campaigns) == 0) {
                return null;
            } 
            else {
                return $campaignsResponse->Campaigns[0];
            }
        }

        return $existingCampaign;
    }
}
