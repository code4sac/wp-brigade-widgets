# Wordpress Widgets for CfA Brigades
A collection of widgets that may be useful to a Code for America Brigade.

### Install Widgets
1. clone into wp-content/plugins
2. Activate Widget in wp-admin -> Plugins
3. Add widget to page in wp-admin -> Appearance -> Widgetss
4. Install Advanced Custom Fields wordpress plugin [ACF Page](http://wordpress.org/plugins/advanced-custom-fields/)

## GitHub Widget
This wordpress widget displays information about specific GitHub repositories. Can help organize projects 
the brigade website so members know what is available to work on. You will need a GitHub API client_id and client_secret

### Configure GitHub Wdget

1. Create ACF Group and text field named, "project_github"
2. Include custom field in page that has widget
3. Custom field should be: username/repositoryname

## Meetup Widget
Simple sidebar widget to display upcoming meetup.com events. You will need a Meetup.com API Key

### Configure Meetup Widget
1. Log on to your Meetup.com account
2. Get the name of your meetup group from the URL. i.e. http://www.meetup.com/groupname
3. Enter the group_name into widget configuration

### Created By:
![Code4Sac](http://code4sac.org/coders/wp-content/uploads/cfs_black-e1377505352342.png "Code4Sac")
