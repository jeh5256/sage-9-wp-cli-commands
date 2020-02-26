<?php
use Illuminate\Support\Str;

/**
 * Commands to generate sage template and controller files
 */
class SageCommands
{
    /**
     * Create Sage 9 Template Files.
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the template file.
     *
     * [--c]
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
        $create_controller_file = WP_CLI\Utils\get_flag_value($assoc_args, 'c', $default = false);
        $template_type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', $default = 'page');
        
        if ($template_type !== 'post') {
            $this->generate_template_file($template_name_arg);
        }

        $this->generate_partial_file($template_name_arg, $template_type);
        
        if ($create_controller_file) {
            $this->generate_controller_files($template_name_arg, $template_type);
        }
    }

    /**
     * Create Sage 9 Controller File.
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the template file.
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
     *     wp make_template home-page --type=page --c=true
     *
     * @when after_wp_load
     * @param $args
     * @param $assoc_args
     */
    public function make_controller($args, $assoc_args)
    {
        $template_name_arg = $args[0];
        $template_type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', $default = 'page');
        
        $this->generate_controller_files($template_name_arg, $template_type);
    }
    
    private function generate_template_file($template_name)
    {
        $template_name_kebab = Str::kebab($template_name);
        $template_file_name = "template-{$template_name_kebab}.blade.php";
        $template_partial_name = "content-page-{$template_name_kebab}.blade.php";
        $template_base_directory = $_SERVER['DOCUMENT_ROOT'] . parse_url(get_template_directory_uri())['path'] . '/views';
        $template_full_path = $template_base_directory . '/' . $template_file_name;

        if (!file_exists($template_full_path)) {
            $template_file_handle = fopen($template_full_path, 'x') or die('Cannot open file:  ' . $template_file_name);

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

            WP_CLI::success("Template File {$template_file_name} Created");
        } else {
            WP_CLI::error("Template File {$template_file_name} Already Exists");
        }
    }
    
    private function generate_partial_file($template_name, $type)
    {
        $template_name_kebab = Str::kebab($template_name);
        $partial_name = ($type === 'post') ? "content-single-{$template_name_kebab}.blade.php" : "content-page-{$template_name_kebab}.blade.php";
        $partial_base_directory = $_SERVER['DOCUMENT_ROOT'] . parse_url(get_template_directory_uri())['path'] . '/views/partials';
        $partial_full_path = $partial_base_directory . '/' . $partial_name;
        
        if (!file_exists($partial_full_path)) {
            $template_partial_file_handle = fopen($partial_full_path, 'w') or die('Cannot open file:  ' . $partial_name);

            $template_partial_file_content = <<<EOT
                <?php
                EOT;

            fwrite($template_partial_file_handle, $template_partial_file_content);
            fclose($template_partial_file_handle);

            WP_CLI::success("Partial File {$partial_name} Created");
        } else {
            WP_CLI::error("Partial File {$partial_name} Already Exists");
        }
    }

    /**
     * @param $template_name
     * @param $type
     */
    private function generate_controller_files($template_name, $type)
    {
        $template_name_kebab = Str::kebab($template_name);
        $class_name = str_replace('-', '', ucwords(mb_convert_case($template_name_kebab, MB_CASE_TITLE, 'UTF-8'), '_'));
        $controller_name = ($type === 'post') ? "Single{$class_name}" : "Template{$class_name}";
        $controller_file_name = "{$controller_name}.php";
        $controller_base_directory = $_SERVER['DOCUMENT_ROOT'] . parse_url(get_theme_file_uri())['path'] . '/app/Controllers';
        $controller_full_path = $controller_base_directory . '/'  . $controller_file_name;
        
        if (!file_exists($controller_full_path)) {
            $template_controller_file_handle = fopen($controller_full_path, 'x') or die('Cannot open file:  ' . $controller_file_name);
            $template_controller_file_content = <<<EOT
            <?php
            
            namespace App\Controllers;
            
            use Sober\Controller\Controller;
            
            class {$controller_name} extends Controller
            {
                protected \$acf = [];
            }
            EOT;

            fwrite($template_controller_file_handle, $template_controller_file_content);
            fclose($template_controller_file_handle);

            WP_CLI::success("Controller {$controller_file_name} File Created");
        } else {
            WP_CLI::error("Controller {$controller_file_name} already exists");
        }
    }
}

$instance = new SageCommands();
WP_CLI::add_command('sage', $instance);