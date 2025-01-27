<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = Category::paginate(5);
        return view('admin.categories.index', compact('categories'));
    }
    /**
     * Display Trashed listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function trash()
    {
        $categories = Category::onlyTrashed()->paginate(5);
        return view('admin.categories.index', compact('categories'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.categories.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required|min:5',
            'slug'=>'required|min:5|unique:categories'
        ]);
        $categories = Category::create($request->only('title','description','slug'));
        $categories->childrens()->attach($request->parent_id,['created_at'=>now(), 'updated_at'=>now()]);
        return redirect('/admin/category')->with('message','Category Added Successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Category $category)
    {
        $categories = Category::where('id','!=', $category->id)->get();
        return view('admin.categories.create',['categories' => $categories, 'category'=>$category]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'title'=>'required|min:5',
            'slug'=>'required|min:5|unique:categories'
        ]);
        $category->title = $request->title;
        $category->description = $request->description;
        $category->slug = $request->slug;
        //detach all categories
        $category->childrens()->detach();
        //attach selected categories
        $category->childrens()->attach($request->parent_id,['created_at'=>now(), 'updated_at'=>now()]);
        //save current record into database
        $saved = $category->save();
        //return back to the /add/edit form
        if($saved)
            return redirect('/admin/category')->with('message','Record Successfully Updated!');
        else
            return back()->with('message', 'Error Updating Category');
    }

    public function recoverCat($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        if($category->restore())
            return back()->with('message','Category Successfully Restored!');
        else
            return back()->with('message','Error Restoring Category');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        if($category->childrens()->detach() && $category->forceDelete()){
            return back()->with('message','Category Successfully Deleted!');
        }else{
            return back()->with('message','Error Deleting Record');
        }
    }
    public function fetchCategories($id = 0){

        if($id == 0)
            return Category::all();

        $category =  Category::where('id', $id)->first();
        return $category->childrens;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Category $category)
    {
        if($category->delete()){
            return back()->with('message','Category Successfully Trashed!');
        }else{
            return back()->with('message','Error Deleting Record');
        }
    }
}
