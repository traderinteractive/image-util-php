FROM nubs/phpunit

USER root

RUN pacman --sync --refresh --noconfirm --noprogressbar --quiet
RUN pacman --sync --noconfirm --noprogressbar --quiet imagemagick base-devel

# Get around a bug with docker registry where the owner is root for the home
# user.  See https://github.com/docker/docker/issues/5892.
RUN chown -R build /home/build

USER build

RUN curl -sS https://aur.archlinux.org/packages/ph/php-imagick/php-imagick.tar.gz | tar -xzC $HOME
RUN cd $HOME/php-imagick && makepkg --clean --noconfirm --noprogressbar

USER root

RUN pacman --upgrade --noconfirm --noprogressbar $HOME/php-imagick/php-imagick*.pkg.tar.xz

USER build

CMD ["./build.php"]
