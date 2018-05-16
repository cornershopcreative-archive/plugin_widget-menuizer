=== Plugin Name ===
Contributors: drywallbmb
Tags: menus, widgets, sidebars
Requires at least: 3.9.2
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Unlock the full potential of the WordPress menu system to create mega menus and more by adding sidebar regions and widgets to your menus.


== Description ==

Anything you can do with a widget can now be done inside your menus!

Widget Menuizer makes WordPress menus even more powerful, allowing for easy creation of custom “mega menus” and other fanciness without completely overhauling the menu management system into something unrecognizable.

Upon activation, navigate to the menu management screen under *Appearance > Menus*, and you'll find a new option for *Sidebars* under the familiar Pages, Posts, Links, Categories and Tags list. Here, you can view all the sidebar regions that exist in your currently-active theme. Simply check a box to add a sidebar into your menu the same way you would for any other menu item.

Once in your menu, you'll see a new option for "Container Element," which specifies which HTML tag is wrapped around the sidebars that are output into the menu.

While running Widget Menuizer you also get the ability to define new sidebar regions right from the *Appearance > Widgets* admin screen, so you can easily create new regions just for including in your menus without having to write any custom code for your theme.

**Important:** Because it's possible to put menu widgets inside sidebars, you may see a warning notice if the sidebar region you've put in your menu contains a menu widget. This is because you may have inadvertently created a recursion: if the menu contained in your sidebar is the same menu your sidebar is placed in, you'll have an infinite loop that will do bad, bad things. So be careful.

**New in 1.0** Widget Menuizer now gives site administrators the ability to control the direction widgets flow within the menu, either horizontally or vertically.

**New in 0.6:** Widget Menuizer now provides a way for site administrators to create new Sidebars on the fly from the Widget admin! Now you can create new sidebars for use in your menus without having to edit your theme files or use some other plugin to let you register new sidebars.

== Installation ==

1. Upload the `widget-menuizer` directory to your plugins directory (typically wp-content/plugins)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit Appearance > Menus to add sidebars to your menus (you might have to go into Screen Options to show the Sidebars option)

== Frequently Asked Questions ==

= Why would I want to do this? =

The menu system in WordPress is a powerful but underutilized feature. "Menus" aren't limited to just regular navigation menus, for example -- they can also be great for things like social media icon links.

But the WordPress menu system is also somewhat limited, in that it generally only offers options for links to individual posts (of all types), categories and tags, in addition to a fairly generic "Link" option. If you wanted to have a nice rich dropdown menu that showed some images, descriptions, or anything beyond just a link, you had to resort to something drastic.

With Widget Menuizer, it's easy to build "megu menus" that have whatever you want in them, because the Widget system itself is so incredibly flexible. With this plugin, you can put anything you can put into a widget into a menu -- which is just about anything at all.

= Why am I seeing an 'infinite loop' warning in my menuized sidebar?  =

Because sidebars can contain menus and menus can now contain sidebars, it's possible to accidentally create a problem where WordPress gets stuck in a loop outputting a menu inside a sidebar inside a menu inside a sidebar... etc. The warning message simply indicates that your sidebar contains a menu widget and thus *might* cause such a recursion. At this time, Widget Menuizer can't actually tell if your sidebar contains the exact same menu the sidebar has been placed into -- just that there's some menu in it somewhere.

If the menu widget your sidebar contains is for a different menu than the one your sidebar is living in, you can safely ignoring the warning. If it's the same menu, however, you'll need to make an adjustment or you'll break your site!

= I changed themes and my sidebar disappeared from my menu. What gives?! =

Because the contents of sidebar regions are tied to particular themes (different themes have different regions, after all), if you place a sidebar that belongs to one theme into your menu, and then change themes, the sidebar will not be shown in your menu. *Only sidebars from the active theme can be displayed.*

If you're using a child theme and its regions are defined in the parent, everything should work fine -- so long as you configured the *contents* of those regions in the currently-active (child) theme.

= I don't see 'Sidebars' as an option in the left-hand column of the Edit Menus page after activating this. Where is it? =

In the upper right corner of your window, click on 'Screen Options' and make sure the Sidebars box is checked.

= How can I create a sidebar specifically for use in a menu that doesn't appear elsewhere in my theme? All my theme's sidebars are used elsewhere. =

Now with version 0.6 of Widget Menuizer, when you go to *Appearance > Widgets*, you have the option to "Add a New Sidebar." Use the form to create as many new sidebar regions as you want, which you can then insert into your menus like any other sidebars. These sidebars you create won't appear elsewhere on your site, so they're ideal for populating custom menus.

= How can I make my child menu items show on hover? =

If your theme doesn't natively support showing child menu items on hover, we've got you covered! Starting with version 1.0, in the WordPress admin you can go to Settings > Widget Menuizer and check the “Show on Hover?” option.

== Screenshots ==

1. After installation and activation, "Sidebars" should appear as an option in the menu management screen.
2. Adding a sidebar region from the left-hand column will add it the menu, with several sidebar-specific menu options, including Label Display and Container Element.
3. Options for displaying the menu item's "Navigation Label" -- in many cases it'll be best to set as "None".
4. Options for the HTML5 element used to contain the sidebar region. What you choose here depends on your theme and CSS; you will probably want to set this to whatever wrapper element the sidebar is usually displayed within for optimal appearance. Regardless, you'll probably need to add some CSS to get it looking exactly how you want.

== Changelog ==

= 1.0 =
* OOP refactoring of the whole codebase.
* Introduced Admin Settings page under Settings > Widget Menuizer.
* Added horizontal/vertical widget arrangement control.

= 0.6 =
* Introduced a new feature letting site administrators create new Sidebars from the widgets admin
* Cleaned up some code

= 0.5.8 =
* Adding missing CSS class to correct appearance of settings sections in the WordPress menu editor

= 0.5.7 =
* Changing 'Title Display' to 'Label Display' to be more consistent with the element ('Navigation Label') it refers to

= 0.5.6 =
* Registering the metabox with the list of regions later in the hopes of catching any regions defined in the wrong hook
* Eliminating an E_NOTICE-level error during output of $output being undefined

= 0.5.5 =
* Changing 'attr_title' from a textfield into an option to set where (or whether) to display the title. Also adding a 'none' option to the container.

= 0.5 =
* Initial public release.
