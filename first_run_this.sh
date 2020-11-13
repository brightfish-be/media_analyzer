#!/bin/bash
script_name=$(basename "$0")
init_date=$(date '+%Y-%m-%d')

ask_question(){
    # ask_question <question> <default>
    local ANSWER
    read -r -p "$1 ($2): " ANSWER
    echo "${ANSWER:-$2}"
}

confirm(){
    # confirm <question> (default = N)
    local ANSWER
    read -r -p "$1 (y/N): " -n 1 ANSWER
    echo " "
    [[ "$ANSWER" =~ ^[Yy]$ ]]
}

words_to_camelcase(){
    # words_to_camelcase: my_php_package => MyPhpPackage
    echo "$*" \
    | sed 's/[-_]/ /g' \
    | awk '{
        for(j=1;j<=NF;j++){ 
            $j=toupper(substr($j,1,1)) substr($j,2) 
        }
        print $0;
    }' \
    | sed 's/ //g'
}


# author full name
git_name=$(git config user.name)
author_name=$(ask_question "Author name" "$git_name")

# auther email address
git_email=$(git config user.email)
author_email=$(ask_question "Author email" "$git_email")

# author github username
git_username=$(git config remote.origin.url | cut -d: -f2)
git_username=$(dirname "$git_username")
git_username=$(basename "$git_username")
author_username=$(ask_question "Author username" "$git_username")
package_namespace=$(words_to_camelcase "$author_username")
# package name
current_directory=$(pwd)
folder_name=$(basename "$current_directory")
package_name=$(ask_question "Package name" "$folder_name")
class_name=$(words_to_camelcase "$package_name")

package_description=$(ask_question "Package description" "This is my package $class_name")

echo -e "Author: $author_name ($author_username, $author_email)"
echo -e "Package: $package_name -- $package_description"
echo -e "Suggested Namespace : $package_namespace"
echo -e "Suggested Class Name: $class_name"

echo
files=$(grep -E -r -l "author_|package_" ./*  | grep -v "$script_name")

echo "This script will replace the above values in all relevant files in the project directory and reset the git repository."
echo "$files"
if ! confirm "Modify composer.json and .MD Markdown files?" ; then
    safe_exit 1
fi

echo

for file in $files ; do
    echo "Updating file $file"
    temp_file="$file.temp"
    < "$file" \
      sed "s/author_name/$author_name/g" \
    | sed "s/author_username/$author_username/g" \
    | sed "s/author@email.com/$author_email/g" \
    | sed "s/package_namespace/$package_namespace/g" \
    | sed "s/package_name/$package_name/g" \
    | sed "s/class_name/$class_name/g" \
    | sed "s/package_description/$package_description/g" \
    | sed "s/init_date/$init_date/g" \
    > "$temp_file"
    rm -f "$file"
    mv "$temp_file" "$file"
done

if confirm 'Let this script delete itself (since you only need it once)?' ; then
    echo "Delete $0 !"
    rm -- "$0"
    rm -- README.md
    mv README.template.md README.md
fi

echo "Now run: git commit -a -m 'prepped with $script_name' && git push"
