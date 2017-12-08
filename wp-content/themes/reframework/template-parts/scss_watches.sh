TO_WATCH="";
for dirtyDir in $(ls -d */); do
    d="${dirtyDir%%/}"
    if [ -d "$d/scss/" ]; then
        TO_WATCH=$(echo "$TO_WATCH $d/scss/$d.scss:$d/style.css");
    fi
done
TO_WATCH=$(echo "$TO_WATCH ../assets/scss/style.scss:../style.css");
TO_WATCH=$(echo "$TO_WATCH ../assets/scss/responsive.scss:../assets/css/responsive.css");
sudo sass --watch $TO_WATCH;
