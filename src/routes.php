<?php
use Slim\Http\Request;
use Slim\Http\Response;
use Cocur\Slugify\Slugify;
use App\Blog;
use App\Comment;
use App\Tag;



// DETAIL route
$app->map(['GET', 'POST'], '/blog/{slug}',  function( Request $request, Response $response, array $args) {
    if($request->getMethod() == "POST") {
        $args = array_merge($args, $request->getParsedBody());
        $blog = Blog::where('title_slug', '=', $args['slug'])->firstOrFail();
        $comment = new Comment();
        if(empty($args['name'])) {  // set the user to Anonymous if name was left blank
            $comment->user_name = "Anonymous";
        } else {  // set the user to the name given
            $comment->user_name = $args['name'];
        }
        
        // creates a new comment object if the comment text wasn't empty
        if(!empty($args['comment'])) {         
            $comment->blog_id = $blog['id'];
            $comment->comment_text = $args['comment'];
            $comment->save();
            $url = '/blog/' . $args['slug'];
            return $response->withStatus(302)->withHeader('Location', $url);
        }
        $args['error'] = "Text is required in the comment field.";
    }
    

    $blog = Blog::where('title_slug', '=', $args['slug'])->firstOrFail();
    $comments = Comment::where('blog_id', '=', $blog->id)->get();
    // package up all the information we need for the detail view
    $args['blog'] = $blog;
   
    $tags = $blog->tags;
    $tag_details = [];
    foreach($tags as $tag) {
          array_push($tag_details, [
            "text" => $tag['text'],
            "color" => Color::find($tag['color_id'])->color,
            "id" => $tag['id']
        ]);
    }
    $args['tags'] = $tag_details;
    // CSRF for the form
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $args['csrf'] = [
        $nameKey => $request->getAttribute($nameKey),
        $valueKey => $request->getAttribute($valueKey)
    ];
    // show the detail view with all packaged information being sent
    return $this->view->render($response, 'detail.twig', $args);
});


// NEW Route
$app->map(['GET', 'POST'],'/new', function (Request $request, Response $response, array $args) {
    if($request->getMethod() == "POST") {
        $args = array_merge($args, $request->getParsedBody());
        // if the title and entry contain something, create a new blog with title and entry properties and optional tags
        if(!empty($args['title']) && !empty($args['entry'])) {
            $args = array_merge($args, $request->getParsedBody());
            $blog = new Blog;
            $blog->title = $args['title'];
            $blog->entry_text = $args['entry'];
            $slugify = new Slugify();
            $proposed_slug = $slugify->slugify($args['title']);
            $existing = Blog::where('title_slug', 'LIKE', '%' . $proposed_slug . '%')->get();
            if(count($existing != 0)) {
                $proposed_slug = $proposed_slug . '-';
                $proposed_slug .= count($existing) + 1;
            }
            $blog->title_slug = $proposed_slug;
            $blog->save();

            $tags = $args['tags'];
            foreach($tags as $tag) {
                $exists = Tag::where('text', $tag)->get();
                if(count($exists) == 0) {
                    $new_tag = new Tag;
                    $new_tag->text = strtolower($tag);
                    $all_colors = Color::select('id')->get();
                    $all_colors_count = Color::count();
                    $rand_color = $all_colors[rand(0, $all_colors_count - 1)]["id"];
                    $new_tag->color_id = $rand_color;
                    $new_tag->save();
                    $blog->tags()->attach($new_tag);
                } else {
                    $blog->tags()->attach($exists);
                }
            }
            $url = '/';
            return $response->withStatus(302)->withHeader('Location', $url);
        }
        $args['error'] = " Both title and text are required to submit an entry.";
    }

 
    // CSRF information    
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $args['csrf'] = [
        $nameKey => $request->getAttribute($nameKey),
        $valueKey => $request->getAttribute($valueKey)
    ];
    $args["all_tags"] = Tag::all();

    // display the view for adding a new entry
    return $this->view->render($response, 'new.twig', $args);
})->setName('new');

// EDIT route
$app->map(['GET', 'POST'], '/edit/{slug}',  function( Request $request, Response $response, array $args) {
    if($request->getMethod() == "POST") {
        $args = array_merge($args, $request->getParsedBody());
        //added missing colon after where clause. removed "=" as it doesn't appear to follow stmnt structure
        $blog = Blog::where('title_slug',  $args['slug'])->firstOrFail();
        // if the post was to delete, delete the post after confirmation
        if(!empty($args['delete'])) {
            $blog->delete();
            // redirect back to home if not cancelled
            return $response->withStatus(302)->withHeader('Location', '/');
        } else if(!empty($args['title']) && !empty($args['entry'])) {
            // if it wasn't delete and both title and entry were filled in update the blog with the new information
            $args = array_merge($args, $request->getParsedBody());
            $blog->title = $args['title'];
            $blog->entry_text = $args['entry'];
            $slugify = new Slugify();
            $proposed_slug = $slugify->slugify($args['title']);
            $existing = Blog::where('title_slug', 'LIKE', '%' . $proposed_slug . '%')->get();
            if(count($existing != 0)) {
                $proposed_slug = $proposed_slug . '-';
                $proposed_slug .= count($existing) + 1;
            }
            $blog->title_slug = $proposed_slug;
            $blog->save();
            $blog->tags()->detach();
            $tags = $args['tags'];
            foreach($tags as $tag) {
                $exists = Tag::where('text', $tag)->get();
                if(count($exists) == 0) {
                    $new_tag = new Tag;
                    $new_tag->text = strtolower($tag);
                    $all_colors = Color::select('id')->get();
                    $all_colors_count = Color::count();
                    $rand_color = $all_colors[rand(0, $all_colors_count - 1)]["id"];
                    $new_tag->color_id = $rand_color;
                    $new_tag->save();
                    $blog->tags()->attach($new_tag);
                } else {
                    $blog->tags()->attach($exists);
                }
            }
            $args['blog'] = $blog;
            // send back to the detail view so the user can make sure it looks correct now
            $url = '/blog/' . $proposed_slug;
            return $response->withStatus(302)->withHeader('Location', $url);
        }
        $args['error'] = " Both name and text are required for a blog entry.";
    }

    // CSRF information
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $args['csrf'] = [
        $nameKey => $request->getAttribute($nameKey),
        $valueKey => $request->getAttribute($valueKey)
    ]
    
    $blog = Blog::where('title_slug', '=', $args['slug'])->firstOrFail();
    $args['blog'] = $blog;
    $args["all_tags"] = Tag::all();
    $associated_tags = $blog->tags()->get();
    foreach($associated_tags as $tag) {
        $args["selected_tags"][] = $tag["text"];
    }
    return $this->view->render($response, 'edit.twig', $args);
});

// SORT route which sorts by tags but could sort by other things
$app->get('/sort/{tag}', function (Request $request, Response $response, array $args) {
    $blogs = Tag::where('text', '=', $args['tag'])->firstOrFail()->blogs()->orderBy('created_at', 'DESC')->get();

    // package up all information to send
    $to_send = [];
    foreach($blogs as $blog) {
        $tags = $blog->tags;
        $tag_details = [];
       
        foreach($tags as $tag) {
            array_push($tag_details, [
                "text" => $tag['text'],
                "color" => Color::find($tag['color_id'])->color,
                "id" => $tag['id']
            ]);
        }
        array_push($to_send,[
            "id" => $blog['id'],
            "title" => $blog['title'],
            "created_at" => $blog['created_at'],
            "tags" => $tag_details
        ]);
    }
    
    $args['blogs'] = $to_send;
    // Render a view much like the original home.twig but only showing blog entries containing that tag
    return $this->view->render($response, 'sorrt.twig', $args);  
})->setName('sort');


// ROOT route  - the default viw
$app->get('/', function (Request $request, Response $response, array $args) {

    $blogs = Blog::orderBy('created_at', 'DESC')->get();
   
    // package up all information to send to next view
    $to_send = [];


    foreach($blogs as $blog) {
        $tags = $blog->tags;
        $tag_details = [];

        foreach($tags as $tag) {
            array_push($tag_details, [
                "text" => $tag["text"],
                "color" => Color::find($tag['color_id'])->color
            ]);
        }
        array_push($to_send,[
            "id" => $blog['id'],
            "title" => $blog['title'],
            "created_at" => $blog['created_at'],
            "title_slug" => $blog['title_slug'],
            "tags" => $tag_details
        ]);
    }
    
    $args['blogs'] = $to_send;
    // Render index view
    return $this->view->render($response, 'home.twig', $args);
})->setName('home');
