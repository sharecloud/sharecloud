<?php
final class PreferencesController extends ControllerBase {
	public function onBefore($action = '', array $params) {
		parent::checkAuthentification();	
	}
	
    public function index() {
        $form   = new Form('form-preferences');
        $form->binding = System::getPreferences();
		
        $fieldset = new Fieldset(System::getLanguage()->_('General'));
        
        foreach (System::getPreferences()->getPreferences() as $preference) {
            
            switch($preference->type) {
                case 'numeric':
                    $elem = new Text($preference->key, System::getLanguage()->_($preference->key), false);
                    $elem->valid = 'numeric';
                    break;
                    
                case 'bool':
                    $elem = new Checkbox($preference->key, System::getLanguage()->_($preference->key), false);
                    break;
                    
                case 'string': 
                    $elem = new Text($preference->key, System::getLanguage()->_($preference->key), false);                                
                    break;
            }
            
			// Do we have any further information for this preference?
			$key = $preference->key . '_INFO';
			$info = System::getLanguage()->_($key);
			
			if($info != $key) {
				$fieldset->addElements(new Paragraph($info));	
			}
			
            $elem->binding = new Databinding($preference->key);
            $fieldset->addElements($elem);
			
			
        }

        
        $form->addElements($fieldset);
        
        if(Utils::getPOST('submit', false) !== false) {
            if($form->validate()) {             
                $form->save();
                System::getPreferences()->save();
                
                
                System::getSession()->setData('successMsg', System::getLanguage()->_('PreferencesUpdated'));
                
                System::forwardToRoute(Router::getInstance()->build('PreferencesController', 'index'));
                exit;
            }
        
        } else {
            $form->fill();  
        }
        
        $form->setSubmit(new Button(
			System::getLanguage()->_('Save'),
			'floppy-disk'
		));
        
        $smarty = new Template();
        $smarty->assign('title', System::getLanguage()->_('Preferences'));
        $smarty->assign('heading', System::getLanguage()->_('Preferences'));
        $smarty->assign('form', $form->__toString());
        $smarty->display('form.tpl');
    }
}
?>