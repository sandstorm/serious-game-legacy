# color the prompt according to $SHELL_ENV_DISPLAY variable
case "$SHELL_ENV_DISPLAY" in
    dev*)
        PS1='\e[42m\n=== $SHELL_ENV_DISPLAY ===\e[m\n\u@\h:\w\$ '
        ;;
    staging*)
        PS1='\e[43m\n=== $SHELL_ENV_DISPLAY ===\e[m\n\u@\h:\w\$ '
        ;;
    *)
        PS1='\e[41m\n=== $SHELL_ENV_DISPLAY ===\e[m\n\u@\h:\w\$ '
        ;;
esac
