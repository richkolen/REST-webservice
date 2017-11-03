<?php

include_once("connect.php");
include_once("functions.php");

$specify_uri = "/webservice/bookservice/books/";
$build_col_link =  "http://$_SERVER[HTTP_HOST]$specify_uri";

$book_array = array();
$all_books = array();
$links = array();
$pagination_array = array();
$pagination_links = array();

$book_query=$mysqli->query("SELECT * FROM books");
$total_books = mysqli_num_rows($book_query);

$current_page = 1;
$total_pages = 1;
$current_books = $total_books;

$message = "";
$unsupported_format = json_encode(array("message"=>"Incorrect format or empty values"));

$echoData = 0;

if($_SERVER['REQUEST_METHOD'] == "GET")
{
    if(isset($_GET['bookid'])) //Haalt de details op voor elk boek op de pagina
    {

        //Toont de content in json
        $result=$mysqli->query("SELECT * FROM books WHERE id = {$_GET['bookid']}");


        while ($rows = $result->fetch_assoc())
        {

            $build_self_link = "http://$_SERVER[HTTP_HOST]$specify_uri" . $rows['id'];

            $self_link = array("rel"=>"self","href"=>$build_self_link);
            $collection_link = array("rel"=>"collection","href"=>$build_col_link);

            array_push($links, $self_link);
            array_push($links, $collection_link);

            $rows['links'] = $links;
            $book_array = $rows;

            $links = array();
        }

        if(count($book_array) == 0)
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
        }
        else
        {
            $echoData = $book_array;
        }

    }
    else //pak anders de collectie zonder limit
    {

        if(isset($_GET['limit']))//pak de collectie met limit
        {
            if(isset($_GET['start']))//pak de collectie met limit en start
            {
                $start = $_GET['start'] - 1;

                //Toont de content in json
                $result=$mysqli->query("SELECT id, title, description, date FROM books LIMIT {$start},{$_GET['limit']}");
            }
            else
            {
                $result=$mysqli->query("SELECT id, title, description, date FROM books LIMIT 0,{$_GET['limit']}");
            }

            while ($rows = $result->fetch_assoc())
            {

                $build_self_link = "http://$_SERVER[HTTP_HOST]$specify_uri" . $rows['id'];

                $self_link = array("rel"=>"self","href"=>$build_self_link);
                $collection_link = array("rel"=>"collection","href"=>$build_col_link);

                array_push($links, $self_link);
                array_push($links, $collection_link);

                $rows['links'] = $links;
                $book_array[] = $rows;

                $links = array();
            }


            //Bereken aantal pagina's met behulp van limit
            $books_mod_result = $total_books % $_GET['limit'];
            $books_mod_result = $total_books - $books_mod_result;

            if($total_books % $_GET['limit'] == 0)
            {
                $total_pages = $total_books / $_GET['limit'];
            }
            else
            {
                $total_pages = $books_mod_result / $_GET['limit'];
                $total_pages++;
            }

            //Bereken de boeken op de laatste pagina
            if($total_books % $_GET['limit'] == 0)
            {
                $books_on_last_page = $total_books - $_GET['limit'];
                $books_on_last_page++;
            }
            else
            {
                $books_on_last_page = $total_books % $_GET['limit'];
                $books_on_last_page = $total_books - $books_on_last_page;
                $books_on_last_page++;
            }

            $previous_book_count = 1;
            $next_book_count = 1 + $_GET['limit'];

            //Bereken de huidige pagina
            if(isset($_GET['start']))
            {

                $books_mod_result_start = $_GET['start'] % $_GET['limit'];
                $books_mod_result_start = $_GET['start'] - $books_mod_result_start;
                $books_mod_result_start = $books_mod_result_start / $_GET['limit'];

                $current_page = $books_mod_result_start + 1;
            }

            //Bereken de vorige/volgende boek start
            if(isset($_GET['start']))
            {

                if($_GET['start'] == 1)
                {
                    $previous_book_count = 1;
                }
                else
                {
                    $previous_book_count = $_GET['start'] - $_GET['limit'];
                }

                $next_book_count = $_GET['start'] + $_GET['limit'];
                if($next_book_count > $total_books )
                {
                    $next_book_count = $_GET['start'];
                }

            }

            //Bereken de volgende pagina
            if ($current_page == $total_pages)
            {
                $next_page = $current_page;
            }
            else
            {

                $next_page = $current_page+1;
            }

            //Bereken de vorige pagina
            if($current_page == 1)
            {
                $previous_page = 1;
            }
            else
            {
                $previous_page = $current_page-1;
            }


            //Bereken het aantal boeken per pagina met limit
            $current_books = mysqli_num_rows($result);

            //Links
            $build_first_link = "http://$_SERVER[HTTP_HOST]$specify_uri?start=" . 1 . "&limit=" . $_GET['limit'];
            $build_last_link = "http://$_SERVER[HTTP_HOST]$specify_uri?start=" . $books_on_last_page . "&limit=" . $_GET['limit'];
            $build_previous_link = "http://$_SERVER[HTTP_HOST]$specify_uri?start=" . $previous_book_count . "&limit=" . $_GET['limit'];
            $build_next_link = "http://$_SERVER[HTTP_HOST]$specify_uri?start=" . $next_book_count . "&limit=" . $_GET['limit'];


            //Link array
            $first_link = array("rel"=>"first","page"=>1,"href"=>$build_first_link);
            $last_link = array("rel"=>"last","page"=>$total_pages,"href"=>$build_last_link);
            $previous_link = array("rel"=>"previous","page"=>$previous_page,"href"=>$build_previous_link);
            $next_link = array("rel"=>"next","page"=>$next_page ,"href"=>$build_next_link);


        }
        else
        { //Laat collectie zien
            $result=$mysqli->query("SELECT id, title, description, date FROM books");

            while ($rows = $result->fetch_assoc())
            {

                $build_self_link = "http://$_SERVER[HTTP_HOST]$specify_uri" . $rows['id'];

                $self_link = array("rel"=>"self","href"=>$build_self_link);
                $collection_link = array("rel"=>"collection","href"=>$build_col_link);

                array_push($links, $self_link);
                array_push($links, $collection_link);

                $rows['links'] = $links;
                $book_array[] = $rows;



                $links = array();
            }

            //Wanneer er geen limit is
            $build_first_link = "http://$_SERVER[HTTP_HOST]$specify_uri";
            $build_last_link = "http://$_SERVER[HTTP_HOST]$specify_uri";
            $build_previous_link = "http://$_SERVER[HTTP_HOST]$specify_uri";
            $build_next_link = "http://$_SERVER[HTTP_HOST]$specify_uri";

            $first_link = array("rel"=>"first","page"=>"1","href"=>$build_col_link);
            $last_link = array("rel"=>"last","page"=>"1","href"=>$build_col_link);
            $previous_link = array("rel"=>"previous","page"=>"1","href"=>$build_col_link);
            $next_link = array("rel"=>"next","page"=>"1","href"=>$build_col_link);
        }


        array_push($pagination_links, $first_link);
        array_push($pagination_links, $last_link);
        array_push($pagination_links, $previous_link);
        array_push($pagination_links, $next_link);

        $pagination_array['currentPage'] = $current_page;
        $pagination_array['currentItems'] = $current_books;
        $pagination_array['totalPages'] = $total_pages;
        $pagination_array['totalItems'] = $total_books;
        $pagination_array['links'] = $pagination_links;


        $all_books['items'] = $book_array;
        $all_books['links'][] = array("rel"=>"self","href"=>$build_col_link);

        $all_books['pagination'] = $pagination_array;

        $echoData = $all_books;
    }


    if($_SERVER['HTTP_ACCEPT'] == "application/json")
    {

        $echoData = json_encode($echoData);
        header("content-type:application/json");

    }
    else if ($_SERVER['HTTP_ACCEPT'] == "application/xml")
    {

        if(isset($_GET['bookid']))
        {

            if($echoData != 0)
            {
                $xml_item_content = "";

                while ($item_name = current($echoData))
                {

                    if(key($echoData) == "links")
                    {

                        $xml_item_content = $xml_item_content . "<links><link><rel>self</rel><href>".$echoData['links'][0]['href']."</href></link><link><rel>collection</rel><href>".$echoData['links'][1]['href']."</href></link></links>";

                    }
                    else
                    {
                        $xml_item_content = $xml_item_content . "<". key($echoData) . ">" . $item_name . "</" . key($echoData) . ">" ;
                    }

                    next($echoData);
                }

                $xml_result = '<?xml version="1.0" encoding="utf-8"?>';
                $xml_result = $xml_result . "<book>".$xml_item_content."</book>";
            }
            else
            {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
                $xml_result = 0;
            }

        }
        else
        {
            $xml_item_content = "";
            $xml_items = "";

            for($i=0; $i < count($echoData['items']); $i++)
            {

                while ($item_name = current($echoData['items'][$i]))
                {

                    if(key($echoData['items'][$i]) == "links")
                    {

                        $xml_item_content = $xml_item_content . "<links><link><rel>self</rel><href>".$echoData['items'][$i]['links'][0]['href']."</href></link><link><rel>collection</rel><href>".$echoData['items'][$i]['links'][1]['href']."</href></link></links>";

                    }
                    else
                    {
                        $xml_item_content = $xml_item_content . "<". key($echoData['items'][$i]) . ">" . $item_name . "</" . key($echoData['items'][$i]) . ">" ;
                    }

                    next($echoData['items'][$i]);
                }

                $xml_items = $xml_items . "<item>".$xml_item_content."</item>";
                $xml_item_content = "";
            }

            $xml_pagination = "<currentPage>".$echoData['pagination']['currentPage']."</currentPage><currentItems>".$echoData['pagination']['currentItems']."</currentItems><totalPages>".$echoData['pagination']['totalPages']."</totalPages><totalItems>".$echoData['pagination']['totalItems']."</totalItems>
            <links>
                <link><rel>first</rel><page>".$echoData['pagination']['links'][0]['page']."</page><href>".$echoData['pagination']['links'][0]['href']."</href></link>
                <link><rel>last</rel><page>".$echoData['pagination']['links'][1]['page']."</page><href>".$echoData['pagination']['links'][1]['href']."</href></link>
                <link><rel>previous</rel><page>".$echoData['pagination']['links'][2]['page']."</page><href>".$echoData['pagination']['links'][2]['href']."</href></link>
                <link><rel>next</rel><page>".$echoData['pagination']['links'][3]['page']."</page><href>".$echoData['pagination']['links'][3]['href']."</href></link>
            </links>";

            $xml_result = '<?xml version="1.0" encoding="utf-8"?>';
            $xml_result = $xml_result . "<games><items>".$xml_items."</items><links><link><rel>self</rel><href>".$echoData['links'][0]['href']."</href></link></links><pagination>".$xml_pagination."</pagination></games>";

        }

        header("content-type:application/xml");
        //Toont de content in xml
        $echoData = $xml_result;

    }
    else
    {
        $echoData = "";
        $unsupported_format = array("message"=>"Unsupported format: {$_SERVER['HTTP_ACCEPT']}");
        $message = json_encode($unsupported_format);
        header($_SERVER['SERVER_PROTOCOL'] . ' 415 Unsupported Media Type', true, 415);
        header("content-type:application/json");
    }


}
else if ($_SERVER['REQUEST_METHOD'] == "POST")
{

    //Bepaalt het soort pagina
    if(!isset($_GET["bookid"]))
    {
        //Insert new json into database
        if($_SERVER["CONTENT_TYPE"] == "application/json")
        {

            $client_data_array = json_decode(read_client_data(),1);

            if($client_data_array != "")
            {
                if(isset($client_data_array["title"])&&isset($client_data_array["description"])&&isset($client_data_array["writer"])&&isset($client_data_array["publisher"]) )
                {
                    if(!empty($client_data_array["title"])&&!empty($client_data_array["description"])&&!empty($client_data_array["writer"])&&!empty($client_data_array["publisher"]) )
                    {

                        $mysqli->query("INSERT INTO books(`title`, `description`, `writer`, `publisher`)
                                        VALUES ('".$client_data_array["title"]."','".$client_data_array["description"]."','".$client_data_array["writer"]."','".$client_data_array["publisher"]."')");
                        header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created', true, 201);

                    }
                    else
                    {

                        $message = $unsupported_format;
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                    }
                }
                else
                {
                    $message = $unsupported_format;
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                }
            }
            else
            {
                $message = $unsupported_format;
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }
        }


        //Voegt nieuwe url encoded in database
        if($_SERVER["CONTENT_TYPE"] == "application/x-www-form-urlencoded")
        {


            if(isset($_POST["title"])&&isset($_POST["description"])&&isset($_POST["writer"])&&isset($_POST["publisher"]) )
            {
                if(!empty($_POST["title"])&&!empty($_POST["description"])&&!empty($_POST["writer"])&&!empty($_POST["publisher"]) )
                {

                    $mysqli->query("INSERT INTO books(`title`, `description`, `writer`, `publisher`)
                                    VALUES ('".$_POST["title"]."','".$_POST["description"]."','".$_POST["writer"]."','".$_POST["publisher"]."')");

                    header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created', true, 201);
                }
                else
                {

                    $message = $unsupported_format;
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                }
            }
            else
            {
                $message = $unsupported_format;
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }

        }

    }
    else
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
    }
}
else if ($_SERVER['REQUEST_METHOD'] == "PUT")
{

    if(isset($_GET['bookid']))
    {

        //Update database met json
        if($_SERVER["CONTENT_TYPE"] == "application/json")
        {

            $client_data_array = json_decode(read_client_data(),1);


            if($client_data_array != "")
            {
                if(isset($client_data_array["title"])&&isset($client_data_array["description"])&&isset($client_data_array["writer"])&&isset($client_data_array["publisher"]) )
                {
                    if(!empty($client_data_array["title"])&&!empty($client_data_array["description"])&&!empty($client_data_array["writer"])&&!empty($client_data_array["publisher"]) )
                    {

                        $mysqli->query("UPDATE `books` SET `title`='".$client_data_array["title"]."',`description`='".$client_data_array["description"]."',`writer`='".$client_data_array["writer"]."',`publisher`='".$client_data_array["publisher"]."',`date`= current_timestamp WHERE id=".$_GET['bookid']);

                    }
                    else
                    {

                        $message = $unsupported_format;
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                    }
                }
                else
                {
                    $message = $unsupported_format;
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                }
            }
            else
            {
                $message = $unsupported_format;
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }
        }


        //Update x-www-form-urlencoded naar database
        if($_SERVER["CONTENT_TYPE"] == "application/x-www-form-urlencoded")
        {

            $client_data_array = read_client_data();
            parse_str($client_data_array);

            if(isset($title)&&isset($description)&&isset($writer)&&isset($publisher) )
            {
                if(!empty($title)&&!empty($description)&&!empty($writer)&&!empty($publisher) )
                {

                    $mysqli->query("UPDATE `books` SET `title`='".$title."',`description`='".$description."',`writer`='".$writer."',`publisher`='".$publisher."',`date`= current_timestamp WHERE id=".$_GET['gameid']);
                }
                else
                {

                    $message = $unsupported_format;
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
                }
            }
            else
            {
                $message = $unsupported_format;
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }

        }
    }
    else
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
    }



}
else if ($_SERVER['REQUEST_METHOD'] == "OPTIONS")
{

    if(isset($_GET['bookid']))
    {
        header("Allow: GET,PUT,DELETE,OPTIONS");
    }
    else
    {
        header("Allow: GET,POST,OPTIONS");
    }
}
else if ($_SERVER['REQUEST_METHOD'] == "DELETE")
{
    if(isset($_GET['bookid']))
    {
        $mysqli->query("DELETE FROM `books` WHERE `id` = " . $_GET['bookid']);
        header($_SERVER['SERVER_PROTOCOL'] . ' 204 No Content', true, 204);
    }
    else
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
    }
}
else
{
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
}


//Toon data
if(!empty($echoData) && isset($echoData))
{
    echo $echoData;
}

//Toon boodschap
if(!empty($message) && isset($message))
{
    echo $message;
}






