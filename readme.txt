=== Category Sub-Blogs ===
Contributors: shmichael
Donate link: 
Tags: categories, sub-blogs, partitioning, multi-blog
Requires at least: 2.5.0
Tested up to: 2.8.6
Stable tag: 0.1.1

Partition your blog into several sub-blogs using categories. This allows for supplying a unified blog in addition to "specialized" sub-blogs.

== Description ==

**Motivation**

Wordpress offers comprehensive tools for tailoring your content. Categories, for one, allow a reader to focus on content most relevant to him. However, the current category behavior has several shortcomings:

* **Requires expertise** from the reader (he can't access a "ready-made" blog for his topic)
* **Cannot search** within a category (or filter by tag, date etc.). This can be referenced in short as **no closure**.
* **Hard to get the RSS feed** within a category
* **Cannot change blog title** when browsing a specific category (to create a look & feel of a dedicated blog)

An alternative approach, opening several separate blogs has other shortcomings:

* Inability to **aggregate several topics together**
* **No single identity** for author / subscriber
* No easy exploration of other content offered by same author

**Category Sub-Blogs**

The solution offered in this plugin is to treat categories as separate sub-blogs. This consists of the following aspects:

1. Offer **closure while browsing a category**: All filtering actions (e.g. searching, filtering by date or tag) only access posts within the currently selected category.
1. Following a link from outside automatically selects the relevant category.
1. RSS feeds & The blog name change for different category selections.

**Specialized Templates**

In order to enhance the look-and-feel of separate sub-blogs, it is advisable to use custom themes for your categories. Luckily, this is already a built-in feature in wordress. Read [this](http://codex.wordpress.org/Category_Templates).

== Installation ==

1. Unzip `category-sub-blogs.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress admin
1. For best results, it is advised to use [pretty permalinks](http://codex.wordpress.org/Using_Permalinks) starting with %category%. Be advised, that this may have a performance caveat (more info on the [codex page](http://codex.wordpress.org/Using_Permalinks#Structure_Tags)).
1. Read the `Description` & `Other Notes` sections 

== Frequently Asked Questions ==

Currently there are no FAQs. If you have one, don't be shy.

== Changelog ==

= 0.1.1 =
* Page links are now valid (and appear globally for all categories)
* Readme styling and content improved.

= 0.1 =
* [F1RST P0ST!!](http://xkcd.com/269/)

== Known Bugs (& Possible Solutions) ==
1. **Date-based archives are currently not supported** and will break once a category is selected. You can ammend this by having a permalink structure of the type %category%/%year%/%month%/%day%/%postname%. If anyone has an elegant solution for this one, I would be more than happy if you would contact me.
1. **Posts associated with more than one category are not supported** (the reader might switch from one category to the other). The reason is that only one category will show up in the permalink, as described [here](http://codex.wordpress.org/Using_Permalinks#Using_.25category.25_with_multiple_categories_on_a_post.2C_or_.25tag.25). This can be fixed by rewriting all permalinks, which might be implemented in the future.
1. **Category hierarchy is currently not supported**. While this will not break your blog, it does not behave perfectly. When accessing any post, the plugin will filter out content that does not belong to the most specific category in the permalink. 
1. **Selected category should be highlighted, and the main page should appear in the category list**. This is more of a to-do thing.