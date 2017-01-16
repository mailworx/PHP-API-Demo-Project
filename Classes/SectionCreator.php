<?php
// This class will show you how sections can be added to a campaign in mailworx.
namespace mailworx;
include_once 'mx_rest_api.php';
include_once 'Constants.php';
class SectionCreator {
    private $securityContext;
        
    function __construct($securityContext){
        $this->securityContext = $securityContext;
    }

  /*/// Description: Generates the section for the given template into the given campaign.
    /// Parameter templateId: The template Id.
    /// Parameter campaignId:The campaign Id.</param>
    /// Returns: Whether the sections have been created or not.*/
    public function generateSection($templateId, $campaignId){
        // Load all available section definitions for the given template
        $sectionDefinitions = $this->loadSectionDefinition($templateId);
        $sectionCreated = false;

        // There are different types of fields which can be used. Have a look at the constants class.

        // If there are no section definitions we can't setup the campaign.
        if (!is_null($sectionDefinitions) && count($sectionDefinitions) > 0) {
            $sectionCreated = true;
            // Right here we create three different sample sections for our sample campaign.

            // Load the section definition that defines an article
            $defintionArticle = $this->loadSectionDefinitionByName('section', $sectionDefinitions);
            if (!is_null($defintionArticle)) {
                $createSectionArticleRequest = new \mailworx\JSON(false);
                $createSectionArticleRequest->setCredentialsByObject($this->securityContext);
                $createSectionArticleRequest->setMethod('CreateSection');
                $createSectionArticleRequest->setProperty('Language', 'EN');
                $createSectionArticleRequest->setProperty('Campaign', array(
                    '__type' => \mailworx\Constants::CAMPAIGN_TYPE,
                    'Guid' => $campaignId
                ));
                $fieldsToAdd = array();

                // ### BUILD UP THE SECTION ###
                foreach ($defintionArticle->Fields as $field) {
                    if (strcasecmp($field->InternalName, 'description') == 0) {
                        array_Push($fieldsToAdd, array(
                          '__type' => \mailworx\Constants::TEXT_FIELD_TYPE,
                          'InternalName' => $field->InternalName,
                          'UntypedValue'=> 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy "eirmod tempor" invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et <a href="www.mailworx.info">justo</a> duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo dup dolores et ea rebum.  <a href="http://sys.mailworx.info/sys/Form.aspx?frm=4bf54eb6-97a6-4f95-a803-5013f0c62b35">Stet</a> clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
                        ));
                    } 
                    elseif (strcasecmp($field->InternalName, 'productimage') == 0) {
                         $file =  $this->uploadFile("E:\mailworx\image_2016061694924427.png", "criteria.png");
                        if (!is_null($file)) {
                            array_Push($fieldsToAdd, array(
                               '__type' => \mailworx\Constants::MDB_FIELD_TYPE,
                               'InternalName' => $field->InternalName,
                               'UntypedValue' => $file
                            ));
                        }
                    } 
                    elseif (strcasecmp($field->InternalName, 'name') == 0) {
                         array_Push($fieldsToAdd, array(
                             '__type' => \mailworx\Constants::TEXT_FIELD_TYPE,
                             'InternalName' => $field->InternalName,
                             'UntypedValue' => '[%mwr:briefanrede%]'
                         ));
                    }
                }

                $sectionArticle = array(
                    '__type' => \mailworx\Constants::SECTION_TYPE,
                    'Created' => $createSectionArticleRequest->getTime(date('Y-m-d H:i:s')),
                    'SectionDefinitionName' => $defintionArticle->Name,
                    'StatisticName' => 'my first article',
                    'Fields' => $fieldsToAdd
                );
                // ### BUILD UP THE SECTION ###

                $createSectionArticleRequest->setProperty('Section', $sectionArticle);
                // ### CREATE THE SECTION ###
                $createSectionArticleResponse = $createSectionArticleRequest->getData();
                // ### CREATE THE SECTION ###
                $sectionCreated =  $sectionCreated && !is_null($createSectionArticleResponse) && !is_null($createSectionArticleResponse->Guid);
            }
               
             $defintionTwoColumns = $this->loadSectionDefinitionByName('section two columns', $sectionDefinitions);
            if (!is_null($defintionTwoColumns)) {
                $createSectionTwoColumnsRequest = new \mailworx\JSON(false);
                $createSectionTwoColumnsRequest->setCredentialsByObject($this->securityContext);
                $createSectionTwoColumnsRequest->setMethod('CreateSection');
                $createSectionTwoColumnsRequest->setProperty('Language', 'EN');
                $createSectionTwoColumnsRequest->setProperty('Campaign', array(
                  '__type' => \mailworx\Constants::CAMPAIGN_TYPE,
                  'Guid' => $campaignId
                ));
                $fieldsToAdd = array();
                foreach ($defintionTwoColumns->Fields as $field) {
                    if (strcasecmp($field->InternalName, 'atwo_left_image') == 0) {
                        $file =  $this->uploadFile("E:\mailworx\connector.png", "connector.png");
                        if (!is_null($file)) {
                            array_Push($fieldsToAdd, array(
                               '__type' => \mailworx\Constants::MDB_FIELD_TYPE,
                               'InternalName' => $field->InternalName,
                               'UntypedValue' => $file
                            ));
                        }
                    } 
                    elseif (strcasecmp($field->InternalName, 'atwo_left_text') == 0) {
                        array_Push($fieldsToAdd, array(
                                '__type' => \mailworx\Constants::TEXT_FIELD_TYPE,
                                'InternalName' => $field->InternalName,
                                'UntypedValue' => 'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. 
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto ignissim,
qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.'
                        ));
                    } 
                    elseif (strcasecmp($field->InternalName, 'atwo_right_image') == 0) {
                         $file =  $this->uploadFile("E:\mailworx\event-app-qr-code-ticket.png", "event.png");
                        if (!is_null($file)) {
                            array_Push($fieldsToAdd, array(
                               '__type' => \mailworx\Constants::MDB_FIELD_TYPE,
                               'InternalName' => $field->InternalName,
                               'UntypedValue' => $file
                            ));
                        }
                    } 
                    elseif (strcasecmp($field->InternalName, 'atwo_right_text') == 0) {
                        array_Push($fieldsToAdd, array(
                                '__type' => \mailworx\Constants::TEXT_FIELD_TYPE,
                                'InternalName' => $field->InternalName,
                                'UntypedValue' => 'Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit,
sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo.'
                        ));
                    }
                }
                   
                $twoColoumn = array(
                  '__type' => \mailworx\Constants::SECTION_TYPE,
                  'Created' => $createSectionTwoColumnsRequest->getTime( date('Y-m-d H:i:s')),
                  'SectionDefinitionName' => $defintionTwoColumns->Name,
                  'StatisticName' => 'section with two columns',
                  'Fields' => $fieldsToAdd
                );
                $createSectionTwoColumnsRequest->setProperty('Section', $twoColoumn);
                $createSectionTwoColumnsResponse = $createSectionTwoColumnsRequest->getData();
                $sectionCreated = $sectionCreated && !is_null($createSectionTwoColumnsResponse) && !is_null($createSectionTwoColumnsResponse->Guid);
            }
                
             $defintionBanner = $this->loadSectionDefinitionByName('banner', $sectionDefinitions);
            if (!is_null($defintionBanner)) {
                $createSectionBannerRequest = new \mailworx\JSON(false);
                $createSectionBannerRequest->setCredentialsByObject($this->securityContext);
                $createSectionBannerRequest->setMethod('CreateSection');
                $createSectionBannerRequest->setProperty('Language', 'EN');
                $createSectionBannerRequest->setProperty('Campaign', array(
                  '__type' => \mailworx\Constants::CAMPAIGN_TYPE,
                  'Guid' => $campaignId
                ));

                $fieldsToAdd = array();
                foreach ($defintionBanner->Fields as $field) {
                    if (strcasecmp($field->InternalName, 'al_image') == 0) {
                        $file =  $this->uploadFile("E:\mailworx\irated_header_final.jpg", "iratedHeader.jpg");
                            
                        if (!is_null($file) ) {
                            array_Push($fieldsToAdd, array(
                               '__type' => \mailworx\Constants::MDB_FIELD_TYPE,
                               'InternalName' => $field->InternalName,
                               'UntypedValue' => $file
                            ));
                        }
                    } elseif (strcasecmp($field->InternalName, 'al_text') == 0) {
                        array_Push($fieldsToAdd, array(
                                '__type' => \mailworx\Constants::TEXT_FIELD_TYPE,
                                'InternalName' => $field->InternalName,
                                'UntypedValue' => 'Developed in the <a href="http://www.mailworx.info/en/">mailworx</a> laboratory the intelligent and auto-adaptive algorithm <a href="http://www.mailworx.info/en/irated-technology>iRated®</a>
                                                     brings real progress to your email marketing. It is more than a target group oriented approach.
                                                     iRated® sorts the sections of your emails automatically depending on the current preferences of every single subscriber.
                                                     This helps you send individual emails even when you don\'t know much about the person behind the email address.'
                                ));
                    }
                }

                $banner = array(
                  '__type' => \mailworx\Constants::SECTION_TYPE,
                  'Created' => $createSectionBannerRequest->getTime( date('Y-m-d H:i:s')),
                  'SectionDefinitionName' => $defintionBanner->Name,
                  'StatisticName' => 'section with two columns',
                  'Fields' => $fieldsToAdd
                );
                $createSectionBannerRequest->setProperty('Section', $banner);
                $createSectionBannerResponse = $createSectionBannerRequest->getData();
                $sectionCreated = $sectionCreated && !is_null($createSectionBannerResponse) && !is_null($createSectionBannerResponse->Guid);
            }
        }

        return $sectionCreated;
    }

    private function uploadFile($path, $fileName) {
        // Get all files in the mdb for the directory mailworx.
        $getMdbFilesRequest = new \mailworx\JSON(false);
        $getMdbFilesRequest->setCredentialsByObject($this->securityContext);
        $getMdbFilesRequest->setMethod('GetMDBFiles');
        $getMdbFilesRequest->setProperty('Language', 'EN');
        $getMdbFilesRequest->setProperty('Path', 'mailworx');
        $getMdbFilesResponse = $getMdbFilesRequest->getData();
     
        if (is_null($getMdbFilesResponse) || is_null($this->searchFileByName($fileName, $getMdbFilesResponse->{'<Files>k__BackingField'}))) {
            // The file we want to upload
            $handle = fopen($path, "rb");
            $fsize = filesize($path);
            $contents = fread($handle, $fsize);
            $byteArray = array_values(unpack("C*", $contents));

            // Send the data to mailworx
            $fileUploadRequest = new \mailworx\JSON(false);
            $fileUploadRequest->setCredentialsByObject($this->securityContext);
            $fileUploadRequest->setMethod('UploadFileToMDB');
            $fileUploadRequest->setProperty('File', $byteArray); // The picture as byte array.
            $fileUploadRequest->setProperty('Path', 'mailworx'); // The location within the mailworx media database. If this path does not exist within the media data base, an exception will be thrown.
            $fileUploadRequest->setProperty('Name', $fileName);  // The name of the file including the file extension.
            $fileUploadResponse = $fileUploadRequest->getData();

            if (!is_null($fileUploadResponse)) {
                return $fileUploadResponse->{'<FileId>k__BackingField'};
            }
        } 
        else {
            return $this->searchFileByName($fileName, $getMdbFilesResponse->{'<Files>k__BackingField'});
        }
    }

    private function searchFileByName($fileName, $files){
        foreach ($files as $file) {
            if (strcasecmp($file->Name, $fileName) == 0) {
                return $file->Id;
            }
        }

        return null;
    }

    private function loadSectionDefinitionByName($sectionDefinitionName, $sectionDefinitions){
        foreach ($sectionDefinitions as $sectiondefinition) {
            if (strcasecmp($sectiondefinition->Name, $sectionDefinitionName) == 0) {
                return $sectiondefinition;
            }
        }

        return null;
    }

    private function loadSectionDefinition($templateId){
        $sectionDefinitionRequest = new \mailworx\JSON(false);
        $sectionDefinitionRequest->setCredentialsByObject($this->securityContext);
        $sectionDefinitionRequest->setMethod('GetSectionDefinitions');
        $sectionDefinitionRequest->setProperty('Language', 'EN');
        $sectionDefinitionRequest->setProperty('Template',
        array(
            "__type" => \mailworx\Constants::TEMPLATE_TYPE,
            "Guid" => $templateId
        ));

        /* ### DEMONSTRATE SECTION DEFINITION STRUCTURE ###
        Here we use the console application in order to demonstrate the structure of each section definition.
        You need to know the structure in order to be able to create sections on your own.*/

        $sectionDefinitionResponse = $sectionDefinitionRequest->getData();
        if (is_null($sectionDefinitionResponse)) {
            return null;
        } else {
            echo '<div>-------------------------------Section definitions----------------------<br/>';
            for ($i=0; $i < count($sectionDefinitionResponse->SectionDefinitions); $i++) {
                $currentSectionDefinition = $sectionDefinitionResponse->SectionDefinitions[$i];
                echo '<div style="margin-left:20px">+++++++++++++++ Section definition '.($i+1).' +++++++++++++++</div>';
                echo '<div style="margin-left:20px">Name:'.$currentSectionDefinition->Name.'</div>';

                if (count($currentSectionDefinition->Fields) > 0) {
                    for ($j=0; $j < count($currentSectionDefinition->Fields); $j++) {
                        $currentField = $currentSectionDefinition->Fields[$j];
                        $endOftype =  strrpos($currentField->__type, ':');
                        $typeName = substr( $currentField->__type, 0, $endOftype);
                        echo '<div style="margin-left:40px">*********** Field '.($j+1).' ***********</div>';
                        echo '<div style="margin-left:40px">Name: '.$currentField->InternalName.'</div>';
                        echo '<div style="margin-left:40px">Type: '.$typeName.'</div>';

                        if (strcasecmp($typeName, 'SelectionField') == 0) {
                            echo '<div style="margin-left:60px">Selections:</div>';
                            for ($k=0; $k < count($currentField->SelectionObjects); $k++) {
                                 $currentSelection = $currentField->SelectionObjects[$k];
                                 echo '<div style="margin-left:80px">Name: '.$currentSelection->Caption.'</div>';
                                 echo '<div style="margin-left:80px">Value: '.$currentSelection->InternalName.'</div>';
                            }
                        }
                        echo'<div style="margin-left:40px">********************************</div>';
                    }
                } 
                else {
                    echo '<div style="margin-left:20px">No fields found</div>';
                }
                echo '<div style="margin-left:20px">++++++++++++++++++++++++++++++++++++++++++++++++++++</div>';
            }
            echo '------------------------------------------------------------------------</div>';
            // ### DEMONSTRATE SECTION DEFINITION STRUCTURE ###
            return $sectionDefinitionResponse->SectionDefinitions;
        }
    }
}
