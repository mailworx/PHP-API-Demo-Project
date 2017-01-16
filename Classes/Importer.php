<?php
//This class will show you how subscribers can be imported into mailworx.

namespace mailworx;
include_once 'mx_rest_api.php';
include_once 'Constants.php';

class Importer{
    private $securityContext;
    const PROFILE_NAME = 'MyFirstProfile';
    function __construct($securityContext){
        $this->securityContext = $securityContext;
    }

    /* /// Description:Imports the subscribers. 
       /// Returns: Returns a KeyValuePair. The key is the profile id and the value is a list of ids of the imported subscribers.*/ 
    public function importSubscribers() {
        $importSubscribersRequest = new \mailworx\JSON(false);
        $importSubscribersRequest->setCredentialsByObject($this->securityContext);
        $importSubscribersRequest->setMethod('ImportSubscribers');       
       
        // ### HANDLE PROFILE ###
        // Here we handle the profile that will be used as target group later.
        // Load the profile with the given name from mailworx.
        $profile = $this->loadProfile();

        // If there is already a profile for the given name, all subscribers of this group have to be removed.
        if (!is_null($profile)) {
            // This action will take place before the import has started.
            $importSubscribersRequest ->setProperty('BeforeImportActions', array(array(
                    "__type" => \mailworx\Constants::CLEAR_PROFILE_ACTION_TYPE,
                    "Name" => Importer::PROFILE_NAME
            )));
        }
        
        // This action will take place after the subscribers have been imported to mailworx.
        $postSubscriberAction = array(array(
            "__type" => \mailworx\Constants::PROFILE_ADDER_ACTION_TYPE,
            "Name" => Importer::PROFILE_NAME, // A new profile will be created if no profile does exist for the given name in mailworx.
             // ExecuteWith => \mailworx\ExecuteWith::INSERT  Only subscribers which will be added as new subscribers will be assigned to the profile.
             // ExecuteWith => \mailworx\ExecuteWith::UPDATE  Only subscribers which already exist will be assigned to the profile.
             // ExecuteWith => \mailworx\ExecuteWith::INSERT | \mailworx\ExecuteWith::UPDATE  Every imported subscriber will be assigned to the profile.
            "ExecuteWith" => \mailworx\ExecuteWith::INSERT | \mailworx\ExecuteWith::UPDATE
        ));

        // ### HANDLE PROFILE ###

        // ### HANDLE IMPORT PROPERTIES ###
        // ### HANDLE LIST OF SUBSCRIBERS ###
        $importSubscribersRequest->setProperty('Subscribers', $this->getSubscribers());
        // ### HANDLE LIST OF SUBSCRIBERS ###
        $importSubscribersRequest->setProperty('PostSubscriberActions', $postSubscriberAction);
        $importSubscribersRequest->setProperty('DuplicateCriteria', 'email');
        $importSubscribersRequest->setProperty('Language', 'EN');
        // ### HANDLE IMPORT PROPERTIES ###

        // ### DO THE IMPORT ###
        $importSubscribersResponse = $importSubscribersRequest ->getData();
        // ### DO THE IMPORT ###

        // ### HANDLE THE IMPORT RESPONSE ###
        // Here we use our console application in order to show you the results/errors of the import response.
        echo '<div>-------------------------------Import result----------------------';
        echo '<div>Duplicates:'.$importSubscribersResponse->Duplicates.'<div>';
        echo '<div>Erros:'.$importSubscribersResponse->Errors.'<div>';
        echo '<div>Imported:'.$importSubscribersResponse->Imported.'<div>';
        echo '<div>Updated:'.$importSubscribersResponse->Updated.'<div>';

        $importedSubscriberIds = array();

        if (!is_null($importSubscribersResponse->FeedbackData) && count($importSubscribersResponse->FeedbackData)) {
            echo '<div>Feedback data<ul>';
               
            for ($i=0; $i < count($importSubscribersResponse->FeedbackData); $i++) {
                if (is_null($importSubscribersResponse->FeedbackData[$i]->Error)) {
                    array_push($importedSubscriberIds, $importSubscribersResponse->FeedbackData[$i]->AffectedSubscriber);
                    echo'<li>Email: '. $importSubscribersResponse->FeedbackData[$i]->UniqueId.', Id:'.$importSubscribersResponse->FeedbackData[$i]->AffectedSubscriber.'</li>';
                } else {
                    echo '<li>'.$importSubscribersResponse->FeedbackData[$i]->Error.'</li>';
                }
            }
            echo '</ul></div>';
        } else {
            echo 'No feedback data';
        }
        echo '------------------------------------------------------------------</div>';

        // If the profile did not exist at the the first iteration we can now load it.
        if (is_null($profile)) {
            $profile = $this->loadProfile();
        }
        // ### HANDLE THE IMPORT RESPONSE ###

        return array( 'profileId' => $profile->Guid,
                      'importedSubscribers' => $importedSubscriberIds);
    }

    private function getSubscribers(){

        // We build some new sample subscribers here.

        // This is a new subscriber.
		// We set some meta data as well as some custom data for this subscriber.
        $subscriberDetail = array();
        $subscriberDetail["__type"] = \mailworx\Constants::SUBSCRIBER_TYPE;

        // Set the meta data field "OptIn". 
		// If set to true the subscriber will receive newsletters.
        $subscriberDetail["Optin"] = true;

        // Set the meta data field "Mailformat". 
		// \mailworx\Mailformat::MULTIPART ->  The subscriber will receive the newsletter as multipart format.
		// \mailworx\Mailformat::HTML    ->  The subscriber will receive the newsletter as HTML format. 
		// \mailworx\Mailformat::TEXT      ->  The subscriber will receive the newsletter as text format. 
        $subscriberDetail["Mailformat"] = \mailworx\Mailformat::MULTIPART;

        // Set the meta data field "Language".
		// This is the language of the subscriber.
		// If no value is specified here, the language of the security context will be used. 
        $subscriberDetail["Language"] = "EN";

        $ubscriberDetail["Status"] = \mailworx\SubscriberStatus::INACTIVE_IF_ACTIVE;

        // Here we set some custom data fields for this subscriber.

        // If you want to know which fields are available for your account, then call the following method: 
        $this->getFieldsOfAccount();

        // These are te different typ of fields which can be used. Have a look at the constants class.
        // We set some fields with different field types here, just to show how to do it right:       
        $subscriberDetail["Fields"] = array(array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email", // A field with this internal name exists in every mailworx account.
                                                "UntypedValue" => "am@mailworx.info"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "firstname", // A field with this internal name exists in every mailworx account.
                                                "UntypedValue" => "mailworx"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname", // A field with this internal name exists in every mailworx account.
                                                "UntypedValue" => "ServiceCrew"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            ),
                                            array( // A field of the type memo in mailworx is also a textfield
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "note",
                                                "UntypedValue" => "JustPutYourTextRightHere"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "interest",
                                                "UntypedValue" => "interest_politics, interest_economy"
                                                // You can use , or ; here to split the values.
                                                // White spaces don't matter either.
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales"
                                            ));

        $subscriberExample = array();
        $subscriberExample["__type"] = \mailworx\Constants::SUBSCRIBER_TYPE;
        $subscriberExample["Optin"] = false;
        $subscriberExample["Mailformat"] = \mailworx\Mailformat::TEXT;
        $subscriberExample["Status"] = \mailworx\SubscriberStatus::INACTIVE_IF_ACTIVE;
        $subscriberExample["Fields"] = array(array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "max@mustermann.at"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "firstname",
                                                "UntypedValue" => "Max"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Mustermann"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => rand(0, 9999999999)
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => true
                                            ));

        $subscriberExample2 = array();
        $subscriberExample2["__type"] = \mailworx\Constants::SUBSCRIBER_TYPE;
        $subscriberExample2["Optin"] = false;
        $subscriberExample2["Mailformat"] = \mailworx\Mailformat::HTML;;
        $subscriberExample2["Status"] = \mailworx\SubscriberStatus::ACTIVE;
        $subscriberExample2["Language"] = "DE";
        $subscriberExample2["Fields"] = array(array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "musterfrau@test.at"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Musterfrau"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => false
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => 1
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            ));
        $subscriberExample3 = array();
        $subscriberExample3["__type"] = \mailworx\Constants::SUBSCRIBER_TYPE;
        $subscriberExample3["Optin"] = true;
        $subscriberExample3["Mailformat"] = \mailworx\Mailformat::HTML;
        $subscriberExample3["Status"] = \mailworx\SubscriberStatus::ACTIVE;
        $subscriberExample3["Language"] = "EN";
        $subscriberExample3["Fields"] = array(array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "isolde@musterfrau.at"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales;position_mechanic"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Musterfrau"
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => true
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => ""
                                            ),
                                            array(
                                                "__type" => \mailworx\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            )
                                        );

        return array($subscriberDetail, $subscriberExample, $subscriberExample2, $subscriberExample3);
    }

    private function getFieldsOfAccount(){
        $getSubscriberFieldsRequest = new \mailworx\JSON(false);
        $getSubscriberFieldsRequest->setCredentialsByObject($this->securityContext);
        $getSubscriberFieldsRequest->setMethod('GetSubscriberFields');
        $getSubscriberFieldsRequest->setProperty('Language', 'EN');
        // MetaInformation => 1 ->                 Will return predefined fields like tel.nr., email, firstname, lastname, ...
        // CustomInformation => 2 - >              Will return custom defined fields.
        // MetaInformation | CustomInformation =>  Will return all kind of fields.
        $getSubscriberFieldsRequest->setProperty('FieldType', \mailworx\FieldType::META_INFORMATION | \mailworx\FieldType::CUSTOM_INFORMATION );
           
        $subscriberFieldsResponse =  $getSubscriberFieldsRequest->getData();
       
        $fieldCount = count( $subscriberFieldsResponse->Fields);
        if ($fieldCount > 0) {
            echo('<div>-------------------------------Fields----------------------');
            for ($i=0; $i < $fieldCount; $i++) {
                echo '<div style="margin-left:20px">+++++++++++++++ Field '.( $i + 1).' +++++++++++++++';
                $endOftype =  strrpos( $subscriberFieldsResponse->Fields[$i]->__type, ':');
                $typeName = substr( $subscriberFieldsResponse->Fields[$i]->__type, 0, $endOftype);
                echo '<div style="margin-left:20px">Type: '.$typeName.'</div><div style="margin-left:20px">Internalname: '. $subscriberFieldsResponse->Fields[$i]->InternalName.'</div>';
                
                // If the field is of the seletion, the selection fields should also be displayed.
                if ($typeName == 'SelectionField') {
                    $selections = $subscriberFieldsResponse->Fields[$i]->SelectionObjects;
                    $selectionCount =  count($selections);
                    echo '<div>   Selections:<ul style="margin-left:20px">';

                    for ($j=0; $j < $selectionCount; $j++) {
                        echo '<li>'.$selections[$j]->InternalName.'</li>';
                    }

                    echo '</ul></div>';
                }
                echo '+++++++++++++++++++++++++++++++++++++++</div>';
            }
            echo('-------------------------------Fields----------------------</div>');
        }
    }

    private function loadProfile(){
        $getProfilesRequest = new \mailworx\JSON(false);
        $getProfilesRequest->setCredentialsByObject($this->securityContext);
        $getProfilesRequest->setMethod('GetProfiles');
        $getProfilesRequest->setProperty('Language', 'EN');
        $getProfilesRequest->SetProperty('ProfileType', \mailworx\ProfileType::STATIC_TYPE);
        $getProfilesResponse = $getProfilesRequest->getData();
        foreach ($getProfilesResponse->Profiles as $profile) {
            if (strcasecmp(Importer::PROFILE_NAME, $profile->Name) == 0) {
                return $profile;
            }
        }
        return NULL;
    }
}
