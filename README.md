# GenesPHP

A pure PHP genetic algorithms library. The intention is to create a library
without any strange dependency. Pure PHP.

# Usage guide for a simple case

1. Create a *Task* object.

2. **Write your own evaluation function**. This function must accept two
   parameters:
   * A list (the genome). Each item in the list is a gene.
   * An arbitrary object (which is defined by you, as complementary data to
     evaluate the individuals). By default, this object is *None*.

   The function must return a floating value as *fitness* of the individual.

3. Create a population and assign to the *Task* object.

4. Choose the *selection*, *crossover* and *mutation* functions that will be
   used, and assign them to the *Task* object as well.

5. Define the desired execution parameters (generations, execution times, etc.).
   Choose a genetic algorithm and ...

6. Run it!

# More examples

Check the *examples* directory to see the solution of simple problems, using
different ways to encode the genome.

# TODO

- Documentation
- More selection algorithms
- Add multiobjective selector(s)
- More mutation operators
- More crossover aoperators
- User documentation and examples
