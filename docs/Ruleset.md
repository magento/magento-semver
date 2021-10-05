# Classes

Code | Level | Rule
-----|-------|-------
M019 | PATCH | [public] Property overwrite has been added.
M020 | PATCH | [protected] Property overwrite has been added.
M026 | PATCH | [private] Property overwrite has been added.
M028 | PATCH | Method overwrite has been added.
M029 | PATCH | Method overwrite has been removed.
M071 | MINOR | Constant has been added.
M073 | MAJOR | Constant has been removed.
M075 | PATCH | Constant has been moved to parent class or implemented interface.
M091 | PATCH | [public] Method has been moved to parent class.
M093 | PATCH | [public] Property has been moved to parent class.
M094 | PATCH | [protected] Property has been moved to parent class.
M095 | PATCH | [protected] Method has been moved to parent class.
M0122 | MAJOR | Extends has been removed.
M0123 | MAJOR | Implements has been removed.
M0124 | MINOR | Parent has been added.
M0125 | MINOR | Interface has been added.
M0126 | MINOR | New trait has been used.
M0141 | MINOR | @api annotation has been added.
M0142 | MAJOR  | @api annotation has been removed.
M100 | MINOR  | Removed last method parameter(s).
M101 | PATCH | Removed last constructor parameter(s).
M102 | MINOR | [public,protected] Added optional parameter(s).
M102 | PATCH | [private] Added optional parameter(s).
M103 | MINOR | Added a required constructor object parameter.
M111 | MINOR | Added an optional constructor parameter to extendable @api class.
M112 | PATCH | Added an optional constructor parameter.
M113 | MAJOR | [public] Method parameter typing changed.
M114 | MAJOR | [protected] Method parameter typing changed.
M115 | PATCH | [private] Method parameter typing changed.
M120 | MAJOR | [public] Method return typing changed.
M121 | MAJOR | [protected] Method return typing changed.
M122 | PATCH | [private] Method return typing changed.
M127 | MAJOR | Exception has been superclassed.
M129 | MINOR | Exception has been subclassed.
M131 | MAJOR | Superclassed Exception has been added.

# Interface

Code | Level | Rule
-----|-------|-------
M072 | MINOR | Constant has been added.
M074 | MAJOR | Constant has been removed.
M076 | PATCH | Constant has been moved to parent class or implemented interface.
M092 | PATCH | [public] Method has been moved to parent class.
M096 | PATCH | [protected] Method has been moved to parent class.
M0122 | MAJOR | Extends has been removed.
M0127 | MINOR | Added parent to interface.
M100 | MINOR | Removed last method parameter(s).
M102 | MAJOR | Added optional parameter(s).
M116 | MAJOR | Method parameter typing changed.
M123 | MAJOR | Method return typing changed.
M128 | MAJOR | Exception has been superclassed.
M130 | MINOR | Exception has been subclassed.
M132 | MAJOR | Superclassed Exception has been added.

# Trait

Code | Level | Rule
-----|-------|-------
M100 | MINOR | Removed last method parameter(s).
M102 | MINOR | Added optional parameter(s).
M117 | MAJOR | [public] Method parameter typing changed.
M118 | MAJOR | [protected] Method parameter typing changed.
M119 | MAJOR | [private] Method parameter typing changed.
M124 | MAJOR | [public] Method return typing changed.
M125 | MAJOR | [protected] Method return typing changed.
M126 | MAJOR | [private] Method return typing changed.

# Database

Code | Level | Rule
-----|-------|-------
M104 | MAJOR | Table was dropped
M105 | MAJOR | Table chard was changed from %s to %s
M106 | MINOR | Key was dropped. But it can be used for 3-rd parties foreign keys
M107 | MAJOR | Column was removed
M108 | MAJOR | Foreign key removed from declaration but it may have had business logic in onDelete statement
M108 | MAJOR | Foreign key was removed
M109 | MINOR | Whitelist do not have table %s declared in db_schema.xml
M109 | MAJOR | Db Whitelist from module %s was removed
M110 | MAJOR | Module db schema whitelist reduced (%s).
M202 | MINOR | Table was added
M203 | MINOR | Column was added
M204 | MAJOR | Foreign key was added
M205 | MAJOR | Foreign key was changed
M205 | MAJOR | Primary key was added
M206 | MAJOR | Primary key was changed
M207 | MAJOR | Primary key was removed
M208 | MAJOR | Unique key was added
M209 | MAJOR | Unique key was removed
M210 | MAJOR | Unique key was changed
