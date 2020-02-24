<?php
use Illuminate\Support\Str;

class SageCommands
{
//    protected $template_file_contents;
//    protected $page_partial_file_contents;
//    protected $post_partial_file_contents;
//
//    public function __construct()
//    {
//
//    }

    /**
     * Create Template Files.
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the template file.
     *
     * [--c=<c>]
     * : Whether or not to create a controller file
     * ---
     * default: false
     * options:
     *   - true
     *   - false
     * ---
     *
     * [--type=<type>]
     * : Whether to create template files for a post or a page
     * ---
     * default: page
     * options:
     *   - page
     *   - post
     * ---
     *
     * ## EXAMPLES
     *
     *     wp make-template homePage --type=page --c=true
     *
     * @when after_wp_load
     * @param $args
     * @param $assoc_args
     */
    public function make_template($args, $assoc_args)
    {
        $template_name_arg = $args[0];
        $template_name = str_replace('-',' ', $template_name_arg);
        $template_name_kebab = Str::kebab($template_name_arg);
        $create_controller_file = (bool)WP_CLI\Utils\get_flag_value($assoc_args, 'c', $default = false);
        $template_type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', $default = 'page');
        $template_base_directory = $_SERVER['DOCUMENT_ROOT'] . parse_url(get_template_directory_uri())['path'] . '/views';
        $controller_base_directory = $_SERVER['DOCUMENT_ROOT'] . parse_url(get_theme_file_uri())['path'] . '/app/Controllers';

        if (!file_exists($template_base_directory)) {
            mkdir($template_base_directory, 0775, true);
        }

        if ($template_type === 'page') {
            $template_file_name = "template-{$template_name_kebab}.blade.php";
            $template_partial_name = "content-page-{$template_name_kebab}.blade.php";

            if (!file_exists($template_partial_name)) {
                $template_file_handle = fopen($template_base_directory . '/' . $template_file_name, 'x') or die('Cannot open file:  ' . $template_file_name);

                $template_file_content = <<<EOT
                <?php
                
                {{--
                  Template Name: {$template_name} Template
                --}}
                
                @extends('layouts.app')
                
                @section('content')
                
                    @while(have_posts()) @php the_post() @endphp
                        @include('{$template_partial_name}')
                    @endwhile
                
                @endsection
                EOT;

                fwrite($template_file_handle, $template_file_content);
                fclose($template_file_handle);

                $template_partial_file_handle = fopen($template_base_directory . '/partials/' . $template_partial_name, 'w') or die('Cannot open file:  ' . $template_partial_name);

                $template_partial_file_content = <<<EOT
            <?php
            EOT;
                fwrite($template_partial_file_handle, $template_partial_file_content);
                fclose($template_partial_file_handle);

                if ($create_controller_file) {
                    $class_name = str_replace('-', '', ucwords(mb_convert_case($template_name_kebab, MB_CASE_TITLE, 'UTF-8'), '_'));
                    $template_controller_file_handle = fopen($template_base_directory . '/partials/' . $template_partial_name, 'x') or die('Cannot open file:  ' . $template_partial_name);

                    $template_controller_file_content = <<<EOT
                    <?php
                    
                    namespace App\Controllers;
                    
                    use Sober\Controller\Controller;
                    
                    class {$class_name} extends Controller
                    {
                        protected \$acf = [];
                    }
                    EOT;

                    fwrite($template_controller_file_handle, $template_controller_file_content);
                    fclose($template_controller_file_handle);
                }
            } else {
                WP_CLI::error("File {$template_partial_name} already exists");
            }
        } elseif ($template_type === 'post') {
            $template_partial_name = "content-single-{$template_name_kebab}.blade.php";
            $template_partial_file_path = $template_base_directory . '/partials/' . $template_partial_name;

            if (!file_exists($template_partial_file_path)) {
                $template_partial_file_handle = fopen($template_partial_file_path, 'x') or die('Cannot open file:  ' . $template_partial_name);
                $template_partial_file_content = <<<EOT
                <?php
                EOT;

                fwrite($template_partial_file_handle, $template_partial_file_content);
                fclose($template_partial_file_handle);

                if ($create_controller_file) {
                    $class_name = str_replace('-', '', ucwords(mb_convert_case($template_name_kebab, MB_CASE_TITLE, 'UTF-8'), '_'));
                    $controller_name = "Single{$class_name}";
                    $controller_file_name = "{$controller_name}.php";

                    if (!file_exists($controller_base_directory . '/'  . $controller_file_name)) {
                        $template_controller_file_handle = fopen($controller_base_directory . '/'  . $controller_file_name, 'x') or die('Cannot open file:  ' . $template_partial_name);

                        $template_controller_file_content = <<<EOT
                        <?php
                        
                        namespace App\Controllers;
                        
                        use Sober\Controller\Controller;
                        
                        class Single{$class_name} extends Controller
                        {
                            protected \$acf = [];
                        }
                        EOT;

                        fwrite($template_controller_file_handle, $template_controller_file_content);
                        fclose($template_controller_file_handle);
                    } else {
                        WP_CLI::error("File {$controller_file_name} already exists");
                    }
                }
            } else {
                WP_CLI::error("File {$template_partial_name} already exists");
            }
        }
    }
}