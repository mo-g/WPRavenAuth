<?php

namespace WPRavenAuth;
    
class OptionsPage
{
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Lookup Auth Admin', 
            'WPRavenThor', 
            'edit_users', 
            'wpravenauth-admin', 
            array( $this, 'create_admin_page' )
        );
    }
    
    public $available_colleges = array(
                                       'CHRISTS'    => 'Christ\'s',
                                       'CHURCH'     => 'Churchill',
                                       'CLARE'      => 'Clare',
                                       'CLAREH'     => 'Clare Hall',
                                       'CORPUS'     => 'Corpus Christi',
                                       'DARWIN'     => 'Darwin',
                                       'DOWN'       => 'Downing',
                                       'EMM'        => 'Emmanuel',
                                       'FITZ'       => 'Fitzwilliam',
                                       'GIRTON'     => 'Girton',
                                       'CAIUS'      => 'Gonville and Caius',
                                       'HOM'        => 'Homerton',
                                       'HUGHES'     => 'Hughes Hall',
                                       'JESUS'      => 'Jesus',
                                       'KINGS'      => 'King\'s',
                                       'LCC'        => 'Lucy Cavendish',
                                       'MAGD'       => 'Magdalene',
                                       'NEWH'       => 'Murray Edwards',
                                       'NEWN'       => 'Newnham',
                                       'PEMB'       => 'Pembroke',
                                       'PET'        => 'Peterhouse',
                                       'QUEENS'     => 'Queens\'',
                                       'ROBIN'      => 'Robinson',
                                       'SEL'        => 'Selwyn',
                                       'SID'        => 'Sidney Sussex',
                                       'CATH'       => 'St Catharine\'s',
                                       'EDMUND'     => 'St Edmund\'s',
                                       'JOHNS'      => 'St John\'s',
                                       'TRIN'       => 'Trinity',
                                       'TRINH'      => 'Trinity Hall',
                                       'WOLFC'      => 'Wolfson',
                                       );

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        ?>
        <div class="wrap">
            <h2>Lookup Auth Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'raven-auth-group' );   
                do_settings_sections( 'wpravenauth-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'raven-auth-group', // Option group
            'WPRavenAuthOptions' // Option name
        );

        add_settings_section(
            'raven-section', // ID
            'Settings for Lookup Authorization', // Title
            array( $this, 'print_section_info' ), // Callback
            'wpravenauth-admin' // Page
        );  
        
        add_settings_field(
            'colleges', // ID
            'Colleges available for Visibility', // Title
            array( $this, 'colleges_callback' ), // Callback
            'wpravenauth-admin', // Page
            'raven-section' // Section
        );
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Please select what institutions you want to allow to see restricted posts.';
    }

    
    /** 
     * Get the settings option array and print one of its values
     */
    public function colleges_callback()
    {
        $selectedColleges = Config::get('colleges');
        foreach ($this->available_colleges as $id => $college)
        {
            printf(
                   '<input type="checkbox" id="colleges" name="%s[colleges][]" value="%s" %s /> <label>%s</label>  <br>',
                   Config::key(),
                   $id,
                   checked( (is_array($selectedColleges) ? in_array($id, $selectedColleges) : false), TRUE, false ),
                   $college
                   );
        }
    }
}

if( is_admin() )
    $WPRavenAuthSettings = new OptionsPage();
