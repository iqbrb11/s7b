<?
$mpurl = get_template_directory_uri().'/Mycima/';
$cronepage = get_home_url().'/wp-admin/admin.php?page=HUCroneG';
define('HUCrone', $mpurl);
define('hupage', $cronepage);


class HUCrone{

    //////////////////////Setup//////////////////////////////////////////   
    public function Setup() {
        add_action('admin_menu', array($this, 'souq_Genrator'));
    
    }
    public function souq_Genrator() {
        add_menu_page( 'HUCroneG', 'HUCrone' , 'administrator', 'HUCroneG', array($this, 'HUCroneG'), 'dashicons-cart' ); 
    }
    //////////////////////MPheader///////////////////////////////////////   
    public function MPheader(){
        echo '<link href="'.HUCrone.'hu-style.css" rel="stylesheet">';
        echo '<link href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" rel="stylesheet">';
    }
        //////////////////////MPFooter///////////////////////////////////////   
    public function MPFooter(){
        echo '<script type="text/javascript" src="'.HUCrone.'/js/jquery-1.8.3.js"></script>';
        echo '<script type="text/javascript" src="'.HUCrone.'/js/main.js"></script>';

    }



    
    public function HuFetch($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_ENCODING,"");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array(

        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILETIME, true);
        curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 5.1; rv:32.0) Gecko/20100101 Firefox/32.0");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,100);
        curl_setopt($ch, CURLOPT_FAILONERROR,true);
        $data = curl_exec($ch);
        if (curl_errno($ch)){
        $data .= 'Retreive Base Page Error: ' . curl_error($ch);
        }
        else {
        $skip = intval(curl_getinfo($ch, CURLINFO_HEADER_SIZE)); 
        $responseHeader = substr($data,0,$skip);
        $data= substr($data,$skip);
        $info = curl_getinfo($ch);
        if ($info['http_code'] != '200')
        $info = var_export($info,true);
        }
        return $data;
    }

    public function GetBlocks($ur=''){
        $data = $this->HuFetch($ur);
        $main = explode('<div class="Grid--WecimaPosts">', $data)[1];
        $main = explode('<ul class="pagination">', $main)[0];
        $blocks = explode('<div class="GridItem"', $main);
        unset($blocks[0]);
        $notadd=[];
        foreach ($blocks as $key => $block) {
            $blocklink = explode('<a href="', $block)[1];
            $blocklink = explode('"', $blocklink)[0];
            $image = explode('style="background-image:url(', $block)[1];
            $image = explode(')', $image)[0];
            $name = explode('<strong', $block)[1];
            $name = explode('</span>', $name)[0];
            $name = explode('>', $name)[1];
            $name = strip_tags($name);
            if(!post_exists($name)){
                echo'<div class="HuBlock" data-url="'. $blocklink.'">';
                    echo'<div class="image"><img src="'.$image.'"></div>';
                    echo'<strong>'.$name.'</strong>';
                echo'</div>';
                $notadd[]=$blocklink;
            }
        }
        if(!empty($notadd)){
            Echo'<span class="notadd">سحب  </span>';
        }else{
            echo'<div class="allisdone">تم سحب كل الحلقات   </div>';
        }
    }    
    public function getalliks($ur=''){
        $data = $this->HuFetch($ur);
        $main = explode('<div class="Grid--WecimaPosts">', $data)[1];
        $main = explode('<ul class="pagination">', $main)[0];
        $blocks = explode('<div class="GridItem"', $main);
        unset($blocks[0]);
        $alllinks = [];
        foreach ($blocks as $key => $block) {
            $blocklink = explode('<a href="', $block)[1];
            $blocklink = explode('"', $blocklink)[0];
                    $alllinks[] =  $blocklink;

        }
        return $alllinks ;
    }
    public function POSTarray($link=''){
        $POSTdata = $this->HuFetch($link);
        $BigArry=[];
        $hashtags = explode('<hashtags>', $POSTdata)[1];
        $hashtags = explode('</hashtags>', $hashtags)[0];
        $hashtagsa= explode('<a', $hashtags);




        $categories = explode('<div class="Breadcrumb--UX">', $POSTdata)[1];
        $categories = explode('</div>', $categories)[0];
        $categorie_a = explode('<a', $categories);
        $allcats = [];
        foreach ($categorie_a as $key => $vaalue) {
            if (strpos($vaalue, 'category') !== false) {
                    $bb = strip_tags($vaalue);
                    $bb = explode('/">',$bb)[1];
                    $allcats[]=  $bb ;
            }
            if (strpos($vaalue, 'watch') !== false) {
                    $tt = strip_tags($vaalue);
                    $tt = explode('/">',$tt)[1];
                $BigArry['title']=$tt;
            }
        }
                $BigArry['category']=$allcats;

        $keywords=[];
        unset($hashtagsa[0]);
        foreach ($hashtagsa as $key => $value) {
            $hash = strip_tags($value);
            $hash= explode('class="unline">', $hash)[1];
            $keywords[] = $hash ; 
        }
        $BigArry['keywords']=$keywords;
        $mediaDetails = explode('<wecima class="separated--top" ', $POSTdata)[1];
        $title = explode('<div class="Title--Content--Single-begin">', $POSTdata)[1];
        $title = explode('</a>', $title)[0];
        $year = explode('class="unline">',$title)[1];
        $year = explode('</a>',$year)[0];
                $BigArry['year']=$year;

        $title = explode('itemprop="name">', $title)[1];
        $title = explode('(', $title)[0];
        $BigArry['mtitle']=$title;
        ///////////////////////////////////////////////////
        $poster = explode('style="--img:url(', $mediaDetails)[1];
        $poster = explode(')', $poster)[0];
        $BigArry['poster']=$poster;

        /////////////////////////////////////////
        $content =explode('div class="StoryMovieContent">', $mediaDetails)[1];
        $content =explode('</div>', $content)[0];
        $content = strip_tags($content);
        $BigArry['content']=$content;
        ///////////////////////////////////////////////////////////
        $term=explode('<ul class="Terms--Content--Single-begin">', $mediaDetails)[1];
        $term=explode('</ul>', $term)[0];
        $termli=explode('<li>', $term);
        unset($termli[0]);
        foreach ($termli as $key => $value) {
            $Ttype=explode('<span>', $value)[1];
            $Ttype=explode('</span>', $Ttype)[0];

              if($Ttype == 'المسلسل'){
                if(null !== explode('<a', $value)){
                    $ll=explode('href="', $value)[1];
                    $ll=explode('"', $ll)[0];
                }
            }
             $BigArry['serieslink']=$ll;

            $resul = explode('<p dir="auto">', $value)[1];
            $resul = explode('</p>', $resul)[0];
            $pop = [];
            if(null !== explode('<a', $resul)){
                $a7a=explode('<a', $resul);
                foreach ( $a7a  as $key => $a) {
                    $resula = explode('</a', $a)[0];
                    if (strpos($resula, '">') !== false) {
                        $resula = explode('/">', $a)[1];
                        $resula = strip_tags($resula);
                    }

                    if (strpos($resula, '،') !== false) {
                        $resula = explode('،', $a);
                    }
                    $pop[]=$resula ;
                }
            }

            if($Ttype == 'الإسم بالعربي'){
                $BigArry['arname']=$pop[0];

            }elseif($Ttype == 'البلد و اللغة '){
                $BigArry['lang']=$pop;

            }elseif($Ttype == 'المدة'){
                $BigArry['runtime']=$pop[0];

            }elseif($Ttype == 'النوع'){
                    $BigArry['gener']=$pop;

            
            }elseif($Ttype == 'الجودة'){
                    $BigArry['quality']=$pop;

            }elseif($Ttype == 'مواقع التصوير'){
                    $BigArry['country']=$pop[0];

            }




        }
       $trailer =  $this->youtubetrailer($title);
        $BigArry['trailer']=$trailer;
        ///////////////////////////////////////////////////////////////////////////////////////////
        $watchlink = explode('<ul class="WatchServersList">', $mediaDetails)[1];
        $watchlink = explode('</ul>', $watchlink)[0];
        $watchli = explode('<li', $watchlink);
        $watses = [];
            unset($watchli[0]);
            foreach ( $watchli as $key => $value) {
                $url = explode('data-url="', $value)[1];
                $url = explode('">', $url)[0];
                $watses[]=$url;
            }

         $i = 0;
            unset($watses[0]);
        foreach ($watses as $id) {
            $i++;
            $watch[] = array("code" =>'<iframe width="100%" height="460" src="'. $id.'" SCROLLING="OFF" frameborder="0" allowfullscreen></iframe>', "name_server" => "سيرفر " . $i . "");
        }  
        $BigArry['servers']=$watch;

        ///////////////////////////////////////////////////////////////////////////////////////////
        $Downlink = explode('<ul class="List--Download--Wecima--Single">', $mediaDetails)[1];
        $Downlink = explode('</ul>', $Downlink)[0];
        $downli = explode('<li', $Downlink);
        $aldowns = [];
        unset($downli[0]);
            foreach ( $downli as $key => $value) {
                $url = explode('href="', $value)[1];
                $url = explode('">', $url)[0];
                $aldowns[]=$url;
            }

         $i = 0;
        foreach ($aldowns as $id) {
            $i++;
            $downs[] = array("link" =>$id, "name" => "سيرفر " . $i . "");
        }  

        $BigArry['Download']=$downs;


        if(isset(explode('<ul class="Inner--List--Teamwork">', $mediaDetails)[1])){
            $actors = explode('<ul class="Inner--List--Teamwork">', $mediaDetails)[1];
            $actors = explode('</ul>', $actors)[0];

            $alactor = explode('<li', $actors);
                unset($alactor[0]);
                $allactors = [];
            foreach ($alactor as $key => $value) {
                $d = explode('<span dir="auto">', $value)[1];
                 $d = explode('</span>', $d)[0];
                 $allactors[]= $d ;
            }
            $BigArry['actors']=$allactors;
        }

        //////////////////////////////////////////////////////////////////////
        if(isset(explode('<singlesection class="Series--Section">', $mediaDetails)[1])){
            $seriesSec  = explode('<singlesection class="Series--Section">', $mediaDetails)[1];
            $seriesSec  = explode('</singlesection>', $seriesSec)[0];

            $SERIES = explode('h2>', $seriesSec)[1];
            $SERIES = explode('</h2>', $SERIES)[0];
            $SERIES = explode('</i>', $SERIES)[1];
            $SERIES = explode('موسم', $SERIES)[0];
            $SERIES = explode('حلقات ', $SERIES)[1];
            $SERIES = explode('</', $SERIES)[0];

            $BigArry['series']=$SERIES;

            $season = explode('<div class="List--Seasons--Episodes">', $seriesSec)[1];
            $season = explode('</div>', $season)[0];

            $season = explode('<a class="selected"', $season)[1];
            $season = explode('</a>', $season)[0];
            $season = explode('">', $season)[1];

            $BigArry['season']=$season;
            ////////////////////////////////////////////////////////////

            $epsode = explode('<a class="hoverable activable selected"', $seriesSec)[1];
            $epsode = explode('</a>', $epsode)[0];

            $epsode = explode('</episodetitle>', $epsode)[0];
            $epsode =  strip_tags($epsode);
            $epsode = explode('">',$epsode)[1];
            $epsode = explode('الحلقة',$epsode)[1];
            $BigArry['epsodes']=$epsode;
        }
        $BigArry = $BigArry ;
        return $BigArry;
    }

     public function SERIESARRAY($link=''){
         $BigArry= [];
        $POSTdata = $this->HuFetch($link);
                $mediaDetails = explode('<wecima class="separated--top" ', $POSTdata)[1];
         $title = explode('<div class="Title--Content--Single-begin">', $POSTdata)[1];
        $title = explode('</a>', $title)[0];
        $title = explode('h1>', $title)[1];
        $title = explode('(', $title)[0];
        $BigArry['mtitle']=$title;
        //////////////////////////////////////////////////////////////////////
        if(isset(explode('<div class="Seasons--Episodes">', $mediaDetails)[1])){
            $seriesSec  = explode('<div class="Seasons--Episodes">', $mediaDetails)[1];
            $seriesSec  = explode('<singlesections>', $seriesSec)[0];

            $season = explode('<div class="List--Seasons--Episodes">', $seriesSec)[1];
            $season = explode('</div>', $season)[0];

            $season = explode('<a class="', $season);
            $sss=[];
            unset($season[0]);
            foreach ($season as $key => $ss) {
                $seasonname = explode('</a>', $ss)[0];
                $seasonname = explode('">', $seasonname)[1];

                $link = explode('href="', $ss)[1];
                $link = explode('"', $link)[0];
                $sss[$key]['name']=$seasonname;
                $sss[$key]['link']=$link;
            }

            $BigArry['season']=$sss;
            ////////////////////////////////////////////////////////////

            $epsode = explode('<a class="hoverable', $seriesSec);
             unset($epsode[0]);
             $eee=[];
            foreach ($epsode as $key => $rr) {
                $epsodename = explode('</episodetitle>', $rr)[0];
                $epsodename =  strip_tags($epsodename);
                $epsodename = explode('">',$epsodename)[1];
                $epsodename = explode('الحلقة',$epsodename)[1];

                $link = explode('href="', $ss)[1];
                $link = explode('"', $link)[0];
                $sss[$key]['name']=$epsodename;
                $eee[$key]['link']=$link;
            }



           // $BigArry['epsodes']=$eee;
        }
        $BigArry = $BigArry ;
        return $BigArry;
    }
    public function gst_s_eps($link=''){
                 $BigArry= [];
        $POSTdata = $this->HuFetch($link);
                $mediaDetails = explode('<wecima class="separated--top" ', $POSTdata)[1];
         $title = explode('<div class="Title--Content--Single-begin">', $POSTdata)[1];
        $title = explode('</a>', $title)[0];
        $title = explode('h1>', $title)[1];
        $title = explode('(', $title)[0];
        $BigArry['mtitle']=$title;
        //////////////////////////////////////////////////////////////////////
        if(isset(explode('<div class="Seasons--Episodes">', $mediaDetails)[1])){
            $seriesSec  = explode('<div class="Seasons--Episodes">', $mediaDetails)[1];
            $seriesSec  = explode('<singlesections>', $seriesSec)[0];
            ////////////////////////////////////////////////////////////

            $epsode = explode('<a class="hoverable', $seriesSec);
             unset($epsode[0]);
             $eee=[];
            foreach ($epsode as $key => $rr) {
                $epsodename = explode('</episodetitle>', $rr)[0];
                $epsodename =  strip_tags($epsodename);
                $epsodename = explode('">',$epsodename)[1];
                $epsodename = explode('الحلقة',$epsodename)[1];

                $link = explode('href="', $rr)[1];
                $link = explode('"', $link)[0];
                $eee[$key]['name']=$epsodename;
                $eee[$key]['link']=$link;
            }



            $BigArry['epsodes']=$eee;
        }
        $BigArry = $BigArry ;
        return $BigArry;

    }
    public function HUCroneG(){
        echo'<div class="Hucodimgall">';
            echo'<div class="Hucodimgall-right">';
           echo'<div class="RightSideFlex"><a href="'.hupage.'"><div class="HeaderLogo"><i class=""></i>WECIMA<h1>سحب افلام و مسلسلات</h1></div></a></div>';
            echo'<form-search class="search--userarea--rightbar"><form action="'.hupage.'&&iner=search" method="POST"><input type="text" name="s" autocomplete="off" autocorrect="off" spellcheck="false" placeholder="إبحث فى ماي سيما"><button><i class="fa fa-search"></i></button></form></form-search>';
            echo'<ul class="menu-userarea--rightbar">';
                echo'<li class="selected"><a href="'.hupage.'"><i class="fas fa-home colorui--home"></i><span>الصفحة الرئيسية</span></a></li>';
                echo'<li><a href="'.hupage.'&iner=post"><i class="fas fa-film colorui--movies"></i><span>سحب المقال  </span></a></li>';
                echo'<li><a href="'.hupage.'&iner=series"><i class="far fa-tv-retro colorui--seriestv"></i><span>مسلسلات</span></a></li>';
            echo'</ul>';
            echo'<div class="crones">';
                echo'<div class="formbox">';
                    echo'<label>تفعيل السحب التلقائي</label>';
                    $op = get_option('crone');
                    if($op == 'true'){
                        $x = 'checked="checked"' ;
                    }else{
                        $x='';
                    }
                   echo'<div class="button r" id="button-1">';
                      echo'<input type="checkbox" class="checkbox" '.$x.'>';
                      echo'<div class="knobs"></div>';
                      echo'<div class="layer"></div>';
                    echo'</div>';
                echo'</div>';
                echo'</div>';
                echo'<copyright>Hucoding</copyright>';
            echo'</div>';
            echo'<div class="Hucodimgall-left">';
                echo'<div class="HUCroneG-bar" data-ajax="'.admin_url('admin-ajax.php').'">';
                if(!isset($_GET['iner'])){    
                    echo'<div class="pagefetch">';
                        echo'<div class="formbox">';
                            echo'<label>{ابط الصفحه  </label>';
                            echo'<input type="text" name="pagenumber"  value="https://weciima.makeup/category/%d9%85%d8%b3%d9%84%d8%b3%d9%84%d8%a7%d8%aa/%d9%85%d8%b3%d9%84%d8%b3%d9%84%d8%a7%d8%aa-%d8%b1%d9%85%d8%b6%d8%a7%d9%86-ramadan-2021/">';
                        echo'</div>';
                        echo'<div class="Generate">سحب   </div>';
                    echo'</div>';    
                            echo'<p class="note"><i class="fas fa-engine-warning"></i> ادخل رابط الصفحه المراد سحب المواضيه منها   مثال  :  الصفحه الرئيسيه  / صفحه ارشيف   .</div>';
                }
                    /*//////////////////////////////////////////////////////*/ 
                     if(isset($_GET['iner']) && $_GET['iner']=="post"){           
                    echo'<div class="postfetch">';
                        echo'<div class="formbox">';
                            echo'<label>الرابط  </label>';
                            echo'<input type="link" name="postfetch">';
                        echo'</div>';
                        echo'<div class="Generate">سحب  </div>';
                    echo'</div>';
                   echo'<p class="note"><i class="fas fa-engine-warning"></i>ادهل رابط المقال  المراد سحبه  .. وتاكد  انه يحتوي علي كلمه  watch </div>';
                }
                    /*//////////////////////////////////////////////////////*/           
                     if(isset($_GET['iner']) && $_GET['iner']=="search"){
                        if(isset($_POST['s']) && $_POST['s']!== ''){
                            $s = $_POST['s'];
                        }else{
                            $s="";
                        }           
                    echo'<div class="SEARCHfetch">';
                        echo'<div class="formbox">';
                            echo'<label>الكلمه  </label>';
                            echo'<input type="link" value="'.$s.'" placeholder="اكتب الكلمه البحثيه " name="SEARCHfetch">';
                        echo'</div>';
                        echo'<div class="Generate">سحب  </div>';
                    echo'</div>';   
                     echo'<p class="note"><i class="fas fa-engine-warning"></i>ادخل الكمه  البح المردا البحث عنها في ماي سيما   </div>';

                  }
                    /*//////////////////////////////////////////////////////*/   
                      if(isset($_GET['iner']) && $_GET['iner']=="series"){           

                        echo'<div class="SERIESFITCH">';
                            echo'<div class="formbox">';
                                echo'<label>الرابط  </label>';
                                echo'<input type="link" name="SERIESFITCH">';
                            echo'</div>';
                            echo'<div class="Generate">سحب  </div>';
                        echo'</div>';   
                         echo'<p class="note"><i class="fas fa-engine-warning"></i>ادخل رابط المسلسل  المراد سحب جميع اجزاؤه </div>';
                         foreach (get_categories(['taxonomy'=>'series']) as $key => $value) {
                            if(get_term_meta($value->term_id,'link',1)!==''){

                             echo'<li link="'.get_term_meta($value->ID,'link',1).'">'.$value->name.'</li>';
                            }
                         }

                    }  
                    /*//////////////////////////////////////////////////////*/     
                echo'<div class="huGrid">';
                echo'</div>';
                echo'</div>';
            echo'</div>';
        echo'</div>';
        


    }  

    public function YDFormatStrings($str)
        {
            $str = strip_tags($str);
            $str = preg_replace("/[ \\t]+/", " ", preg_replace("/\\s*\$^\\s*/m", "\n", $str));
            $str = str_replace(PHP_EOL, "", $str);
            return $str;
        }
    public function youtubetrailer($title){
        $new_str = str_replace(' ', '', $title);
        $youtubeapi='https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=1&q='.$new_str.'trailer&type=video&key=AIzaSyDnO4tnB_xbmSn4lxUk4xGJmGq32F1DRDs';
        $youtube =$this-> HuFetch($youtubeapi);
        $youtube = json_decode($youtube, 1);
        $youtubeid=$youtube['items'][0]['id']['videoId'];
         $youtubetrailer='<iframe width="965" height="543" src="https://www.youtube.com/embed/'.$youtubeid.'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
         return $youtubetrailer ;
    }

    public function insertpost($BigArry){

        if ( ! is_admin() ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }
        if (post_exists($BigArry['title']) == 0 ) {
            $id = wp_insert_post(
                array("post_title" => $BigArry["title"],
                 "post_content" => empty($BigArry["content"]) ? $BigArry["title"] : $BigArry["content"], 
                 "post_type" => "post", 
                 "post_status" => "publish", 
                 "post_author" => 1
                )
            );
         }else{
             $id=  post_exists( $BigArry['title'] ) ;
         }   
        if(isset( $BigArry["servers"])){
             update_post_meta($id, "servers", $BigArry["servers"]);
        }        
        if(isset( $BigArry["runtime"])){
             update_post_meta($id, "runtime", $BigArry["runtime"]);
        }
        if(isset($BigArry["Download"])){
             update_post_meta($id, "downloads", $BigArry["Download"]);
        }
        if(isset( $BigArry["trailer"])){
         update_post_meta($id, "trailer", $BigArry["trailer"]);
        }

        if(isset( $BigArry["epsodes"])){
         update_post_meta($id, "number", $BigArry["epsodes"]);
        }

        if(isset( $BigArry["content"])){
         update_post_meta($id, "story", $BigArry["content"]);
        }
        if(isset( $BigArry["keywords"])){
            wp_set_post_terms($id, array_values($BigArry["keywords"]), "post_tag");
        }
        if(isset($BigArry["gener"])){
         wp_set_object_terms($id,$BigArry["gener"], "genre");
        }        

        if(isset($BigArry["country"])){
         wp_set_object_terms($id,$BigArry["country"], "country");
        }

        if(isset($BigArry["actors"])){
         wp_set_object_terms($id,$BigArry["actors"], "actor");
        }

        if(isset($BigArry["category"])){

        wp_set_object_terms($id, array_values($BigArry["category"]), "category");
    }
            if(isset($BigArry["year"])){

        if (taxonomy_exists("release-year")) {
            wp_set_object_terms($id, $BigArry["year"], "release-year");
        } else {
            if (taxonomy_exists("years")) {
                wp_set_object_terms($id, array_values(explode(",", $BigArry["year"])), "years");
            }
        }
    }
        if (taxonomy_exists("Quality")) {
            wp_set_object_terms($id, array_values($BigArry["quality"]), "Quality");
        } else {
            wp_set_object_terms($id, array_values($BigArry["quality"]), "quality");
        }
        $image_url = $BigArry["poster"];
        $poster = $BigArry['poster'];
        if( !empty($poster) ) {
                $upload_dir = wp_upload_dir();
                $image_data = $this->HuFetch($poster);
                $filename = basename($poster);
                $file = $upload_dir['basedir'] . '/' . $filename;
                file_put_contents($file, $image_data);
                $wp_filetype = wp_check_filetype($filename, null );
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $file);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                //$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                // wp_update_attachment_metadata( $attach_id, $attach_data );
                set_post_thumbnail( $id, $attach_id );
        }



        if (!empty($BigArry['series'])) {
            $serslug = 'series';
            $parent_term = term_exists($BigArry["series"], $serslug);
            if (isset($parent_term["term_id"])) {
                $season_term = term_exists($BigArry["season"], $serslug, $parent_term["term_id"]);
                if (!isset($season_term["term_id"])) {
                    if (!empty($BigArry["season"])) {
                        $season = wp_insert_term($BigArry["season"], $serslug, array("parent" => $parent_term["term_id"]));
                        $termseries = array($parent_term["term_id"], $season["term_id"]);
                    }else{
                        $termseries = array($parent_term["term_id"]);
                    }
                } else {
                    $termseries = array($parent_term["term_id"], $season_term["term_id"]);
                }
                wp_set_post_terms($id, $termseries, $serslug);
                          if(!empty($BigArry['serieslinkr'])){
                         update_term_meta( $parent["term_id"],'link', 1) ;
                        }
            } else {
                $parent = wp_insert_term($BigArry["series"], $serslug, array());
                       if(!empty($BigArry['serieslinkr'])){
                         update_term_meta( $parent["term_id"],'link', 1) ;
                        }
                if (!empty($BigArry["season"])) {
                    $season = wp_insert_term($BigArry["season"], $serslug, array("parent" => $parent["term_id"]));
                    $termseries = array($parent["term_id"], $season["term_id"]);
                }else{
                    $termseries = array($parent["term_id"]);
                }
                wp_set_post_terms($id, $termseries, $serslug);

            }                   
        }
    }






}


function HUadmin_style() {

        (new HUCrone)->MPheader();
        (new HUCrone)->MPFooter();
}
add_action('admin_enqueue_scripts', 'HUadmin_style');


(new HUCrone)->Setup();

class ajaxx {
     public function ajax_actions(){
      $arr = [
        'pagefetch',
        'postfuck',
        'SEARCHfetch',
        'SERIESFITCH',
        'crone',
        'seasonget',
       
      ];
      return $arr;
    }
        public function pagefetch(){
            set_time_limit(10000); // 
        global $wpdb ;
        $number = $_POST['input'];
        $link = 'https://mycima.wecima.cam/'.$number.'/';
        $all =  (new HUCrone)->getalliks($number);
        foreach ($all as $key => $li) {
         $data =  (new HUCrone)->POSTarray($li);
          (new HUCrone)->insertpost($data);
        }
          (new HUCrone)->GetBlocks($number);
        wp_die();
    }
    public function postfuck(){
            set_time_limit(10000); // 

        $link = $_POST['link'];
		$parse = parse_url($link);
		$domain_n = explode(".", $parse['host'])[0];
		if($domain_n!='cimaaa4u'){
         $data =  (new HUCrone)->POSTarray($link);
          (new HUCrone)->insertpost($data);
		if(post_exists($data['title'])){
          echo '<h1>تم تحديث سحب مقال '.$data['title'].'</h1>';
		}else{
          echo '<h1>تم  سحب   '.$data['title'].'</h1>';
		}
		}else{
        $link2 = 'https://mycima.wecima.cam/';
        $all =  (new c4HUCrone)->getalliks($link2);
         $data =  (new c4HUCrone)->POSTarray($link);
		if(post_exists($data['title'])){
          echo '<h1>عفواً تم سحب '.$data['title'].' من قبل</h1>';
		}else{
          (new HUCrone)->insertpost($data);
          echo '<h1>تم  سحب   '.$data['title'].'</h1>';
		}
		}
        wp_die();
    }  

      public function SEARCHfetch(){
            set_time_limit(10000); // 
        global $wpdb ;
        $number = $_POST['input'];
        $link = 'https://mycima.wecima.cam/'.$number.'/';
        $all =  (new HUCrone)->getalliks($link);
        foreach ($all as $key => $li) {
         $data =  (new HUCrone)->POSTarray($li);
          (new HUCrone)->insertpost($data);
        }
                  (new HUCrone)->GetBlocks($link);

        wp_die();
    }
      public function SERIESFITCH(){
            set_time_limit(10000); // 
        $link = $_POST['input'];
         $data =  (new HUCrone)->SERIESARRAY($link);
            if(!empty($data['season'])){
                echo'<div class="HU-SESONS">';
                foreach ($data['season'] as $key => $value) {
                    echo'<li data-link="'.$value['link'].'"> '.$value['name'].'</li>';
                }
                echo'</div>';
            }else{
                $data =  (new HUCrone)->gst_s_eps($link);
        foreach ($data['epsodes'] as $key => $sons) {
            if(!empty($sons['name'])){
            $data =  (new HUCrone)->POSTarray($sons['link']);
          (new HUCrone)->insertpost($data);
            }
        }
    }
            echo'<div class="HU-SESONS-down"></div>';
        wp_die();
    }
      public function seasonget(){
         set_time_limit(500); // 
        $link = $_POST['input'];
        $data =  (new HUCrone)->gst_s_eps($link);
        foreach ($data['epsodes'] as $key => $sons) {
            if(!empty($sons['name'])){
            $data =  (new HUCrone)->POSTarray($sons['link']);
          (new HUCrone)->insertpost($data);
            }
        }
                  (new HUCrone)->GetBlocks($link);

        wp_die();
    }



public function crone(){
            set_time_limit(500); // 

        $link = $_POST['val'];
        update_option('crone',$link);
        echo get_option('crone');
        wp_die();
    
}
}    
foreach ((new ajaxx)->ajax_actions() as $key => $value) {
    add_action( 'wp_ajax_'.$value, array( ( new ajaxx),$value) );
        add_action( 'wp_ajax_nopriv_'.$value, array( ( new ajaxx),$value) );

 }


function my_cron_schedules($schedules){
    if(!isset($schedules["3600"])){
        $schedules["3600"] = array(
            'interval' => 3600, // تكرار كل ساعة
            'display' => __('مرة كل ساعة'));
    }
    if(!isset($schedules["5400"])){
        $schedules["5400"] = array(
            'interval' => 5400, // تكرار كل ساعة ونصف
            'display' => __('مرة كل ساعة ونصف'));
    }
    return $schedules;
}




add_filter('cron_schedules','my_cron_schedules');

if (!wp_next_scheduled('my_task_hook')) {
    wp_schedule_event( time(), '30min', 'my_task_hook' );
}
add_action ( 'my_task_hook', 'my_task_function' );

function my_task_function() {
    if(get_option('crone') == 'false'){

        $link = 'https://cdn1.wecima.club/';
        $all =  (new HUCrone)->getalliks($link);
        foreach ($all as $key => $li) {
         $data =  (new HUCrone)->POSTarray($li);
          (new HUCrone)->insertpost($data);
        }
    }
}