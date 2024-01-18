# TODO

The following list comprises ideas, suggestions, and known issues, all of which are in consideration for possible implementation in future releases.

***This is not a roadmap or a task list.*** Just because something is listed does not necessarily mean it will ever actually get implemented. Some might be bad ideas. Some might be impractical. Some might either not benefit enough users to justify the effort or might negatively impact too many existing users. Or I may not have the time to devote to the task.

* Add ability to configure plugin via defines in wp-config.php. Include option to disable admin.
    * However, if admin is being shown but user/pw is set in file, disable those inputs on the form.
    * (Maybe show smtp username, but definitely don't show pw)
* Don't show or include SMTP pw on form. Leave blank to indicate previously entered pw should be used.
    * (But UI should let user know whether it knows about a pw or not, and if so, that it doesn't need to be re-entered unless it is being changed)
* If use_gmail is enabled, infer the '@gmail.com' as part of the smtp_user if not present
    * Add new `normalize_gmail_user()`
    * Modify the localized 'alert' string to remove the instructions to specify a full Gmail address
* Add hooks
* Rewrite JS to remove jQuery dependency

Feel free to make your own suggestions or champion for something already on the list (via the [plugin's support forum on WordPress.org](https://wordpress.org/support/plugin/configure-smtp/) or on [GitHub](https://github.com/coffee2code/configure-smtp/) as an issue or PR).