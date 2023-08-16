# ~/.bashrc: executed by bash(1) for non-login shells.

PS1="\e[1m\\u@\\h\e[0m:\\w\\$ "
export LS_OPTIONS='--color=auto'
alias ls='ls $LS_OPTIONS'
alias ll='ls $LS_OPTIONS -l'
alias l='ls $LS_OPTIONS -lA'
