<?php

namespace Sudo\Post\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class PostSeedCommand extends Command {

    protected $signature = 'sudo/posts:seeds';

    protected $description = 'Khởi tạo dữ liệu mẫu cho Bài viết';

    public function handle() {
        DB::table('posts')->truncate();
        DB::table('post_categories')->truncate();
        DB::table('post_category_maps')->truncate();
        DB::table('seos')->where('type', 'posts')->delete();
        DB::table('seos')->where('type', 'post_categories')->delete();
        DB::table('system_logs')->where('type', 'posts')->delete();
        DB::table('system_logs')->where('type', 'post_categories')->delete();
        DB::table('language_metas')->where('lang_table', 'posts')->delete();
    	DB::table('language_metas')->where('lang_table', 'post_categories')->delete();
        
        $created_at = $updated_at = date('Y-m-d H:i:s');
        $detail = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</p><ul><li><a href="#">Link</a></li><li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</li><li><a href="#">Link</a></li><li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis tempora pariatur est ipsam nisi saepe maxime quisquam soluta atque beatae rerum illum incidunt, aut harum unde, facilis nobis, consectetur. Inventore.</li></ul><table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td>data</td><td>data</td><td>data</td></tr><tr><td>data</td><td>data</td><td>data</td></tr><tr><td>data</td><td>data</td><td>data</td></tr></tbody></table>';

        // Danh mục
        $post_categories = [];
        $seos = [];
        $lang_metas = [];
        for ($i=0; $i < 10; $i++) {
            $name = 'Danh mục '.$i;
            $post_categories[] = [
                'name' => $name,
                'slug' => str_slug($name),
                'detail' => $detail,
                'status' => 1,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ];
            $seos[] = [
                'type'              => 'post_categories',
                'type_id'           => $i+1,
                'title'             => '',
                'description'       => '',
                'robots'            => 'Index,Follow',
            ];
            $lang_metas[] = [
                'lang_table'        => 'post_categories',
                'lang_table_id'     => $i+1,
                'lang_locale'       => 'vi',
                'lang_code'         => getCodeLangMeta()
            ];
        }
        DB::table('post_categories')->insert($post_categories);
        DB::table('seos')->insert($seos);
        DB::table('language_metas')->insert($lang_metas);
        $this->echoLog('Danh muc bai viet duoc tao thanh cong. So luong: '.count($post_categories));

        // Bài viết
        $stt = 0;
        for ($j=0; $j < 1; $j++) { 
            $posts = [];
            $seos = [];
            $lang_metas = [];
            for ($i=0; $i < 1000; $i++) {
                $stt++;
                $name = 'Bài viết số '.$stt;
                $posts[] = [
                    'name' => $name,
                    'slug' => str_slug($name),
                    'detail' => $detail,
                    'status' => 1,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                ];
                $seos[] = [
                    'type'              => 'posts',
                    'type_id'           => $stt+1,
                    'title'             => '',
                    'description'       => '',
                    'robots'            => 'Index,Follow',
                ];
                $lang_metas[] = [
                    'lang_table'        => 'posts',
                    'lang_table_id'     => $stt+1,
                    'lang_locale'       => 'vi',
                    'lang_code'         => getCodeLangMeta()
                ];
            }
            DB::table('posts')->insert($posts);
            DB::table('seos')->insert($seos);
            DB::table('language_metas')->insert($lang_metas);
        }
        $this->echoLog('Bai viet duoc tao thanh cong. So luong: '.$stt);

        // maps
        $stt = 0;
        for ($j=0; $j < 1; $j++) { 
            $post_category_maps = [];
            for ($i=0; $i < 1000; $i++) {
                $stt++;
                $post_category_maps[] = [
                    'post_id' => $stt,
                    'post_category_id' => rand(1,10),
                ];
            }
            \DB::table('post_category_maps')->insert($post_category_maps);
        }
    }

    public function echoLog($string) {
        $this->info($string);
        Log::info($string);
    }

}