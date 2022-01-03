# dissemination-of-culture

By Nik Stankovic, Jan 2017
Revised by Nik Stankovic, Jan 2022

![Animated](https://github.com/nikslab/dissemination-of-culture/blob/master/animated/15px_10Fx5T__1_9463.gif)

Implementation, visualization and further exploration of experiments in the paper titled **The Dissemination of Culture** by Robert Axelrod (University of Michigan) published in *The Journal of Conflict Resolution* in 1997. (full paper: http://www-personal.umich.edu/~axe/research/Dissemination.pdf).

# CONFIG

Configuration files are placed in config directory

`
{
    "name": "testing",
    "matrix_size": "20",
    "features": "5",
    "traits": "5",
    "reach": "10",
    "mutation_rate": "0.0005",
    "report": "1",
    "save": "yes",
    "gif": "yes",
    "gif_delay": "20"
}
`

name: label for experiment, also used in filenames
matrix_size: in pixels, NxN
features: number of features
traits: number of traits
reach: how far in pixels can you interact with someone
mutation rate: probability that a pixel with mutate in each iteration, note it's not %, percent is probability * 100
report: whether you want intermediate reports while running or not
save> whether the data should be saved
gif: whether an animated gif should be generated
gif_delay: how fast the gif should animate from one iteration to the next


## Requirements

GD library for PHP, installed with e.g. on Linux `sudo apt-get install gf7.4-gd`

## Credits

Using GIFEncoder Version 2.0 by László Zsidi, http://gifs.hu to create animated gifs.
