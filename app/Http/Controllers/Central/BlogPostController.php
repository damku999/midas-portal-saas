<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BlogPostController extends Controller
{
    /**
     * Display a listing of blog posts
     */
    public function index(Request $request)
    {
        $query = BlogPost::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->latest()->paginate(20);

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
        ];

        return view('central.blog-posts.index', compact('posts', 'stats'));
    }

    /**
     * Show the form for creating a new blog post
     */
    public function create()
    {
        return view('central.blog-posts.create');
    }

    /**
     * Store a newly created blog post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'category' => 'required|string',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'reading_time_minutes' => 'nullable|integer|min:1',
        ]);

        // Generate slug
        $validated['slug'] = BlogPost::generateSlug($validated['title']);

        // Handle tags
        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Handle featured image with thumbnail generation
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'blog-posts/' . $filename;
            $thumbPath = 'blog-posts/thumbs/' . $filename;

            // Store original image
            $image->storeAs('blog-posts', $filename, 'public');

            // Create and store thumbnail (400x300)
            $thumbnailImage = Image::read($image->getRealPath());
            $thumbnailImage->cover(400, 300);
            Storage::disk('public')->put($thumbPath, (string) $thumbnailImage->encode());

            $validated['featured_image'] = $path;
            $validated['featured_image_thumb'] = $thumbPath;
        }

        // Set published_at if publishing
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Set author_id
        $validated['author_id'] = auth('central')->id();

        BlogPost::create($validated);

        return redirect()
            ->route('central.blog-posts.index')
            ->with('success', 'Blog post created successfully');
    }

    /**
     * Show the form for editing the specified blog post
     */
    public function edit(BlogPost $blogPost)
    {
        return view('central.blog-posts.edit', compact('blogPost'));
    }

    /**
     * Update the specified blog post
     */
    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'category' => 'required|string',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'reading_time_minutes' => 'nullable|integer|min:1',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $blogPost->title) {
            $validated['slug'] = BlogPost::generateSlug($validated['title']);
        }

        // Handle tags
        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Handle featured image with thumbnail generation
        if ($request->hasFile('featured_image')) {
            // Delete old images (original and thumbnail)
            if ($blogPost->featured_image) {
                Storage::disk('public')->delete($blogPost->featured_image);
            }
            if ($blogPost->featured_image_thumb) {
                Storage::disk('public')->delete($blogPost->featured_image_thumb);
            }

            $image = $request->file('featured_image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'blog-posts/' . $filename;
            $thumbPath = 'blog-posts/thumbs/' . $filename;

            // Store original image
            $image->storeAs('blog-posts', $filename, 'public');

            // Create and store thumbnail (400x300)
            $thumbnailImage = Image::read($image->getRealPath());
            $thumbnailImage->cover(400, 300);
            Storage::disk('public')->put($thumbPath, (string) $thumbnailImage->encode());

            $validated['featured_image'] = $path;
            $validated['featured_image_thumb'] = $thumbPath;
        }

        // Set published_at if changing to published
        if ($validated['status'] === 'published' && $blogPost->status !== 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $blogPost->update($validated);

        return redirect()
            ->route('central.blog-posts.index')
            ->with('success', 'Blog post updated successfully');
    }

    /**
     * Toggle the status of the blog post
     */
    public function toggleStatus(BlogPost $blogPost)
    {
        $newStatus = $blogPost->status === 'published' ? 'draft' : 'published';

        // If publishing for the first time, set published_at
        if ($newStatus === 'published' && !$blogPost->published_at) {
            $blogPost->published_at = now();
        }

        $blogPost->status = $newStatus;
        $blogPost->save();

        return redirect()
            ->back()
            ->with('success', 'Blog post status updated to ' . $newStatus);
    }

    /**
     * Remove the specified blog post
     */
    public function destroy(BlogPost $blogPost)
    {
        // Delete featured images (original and thumbnail) if exist
        if ($blogPost->featured_image) {
            Storage::disk('public')->delete($blogPost->featured_image);
        }
        if ($blogPost->featured_image_thumb) {
            Storage::disk('public')->delete($blogPost->featured_image_thumb);
        }

        $blogPost->delete();

        return redirect()
            ->route('central.blog-posts.index')
            ->with('success', 'Blog post deleted successfully');
    }
}
