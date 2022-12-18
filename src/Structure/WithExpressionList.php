<?php

namespace Se\Mdx\Structure;

use Se\Mdx\Exception\NoWithExpressionFoundException;

class WithExpressionList
{
    private array $expressions;

    public function __construct()
    {
        $this->expressions = [];
    }

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * @param WithExpression $expression
     * @return WithExpressionList
     */
    public function addExpression(WithExpression $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }

    /**
     * @param string $alias
     * @return WithExpression
     * @throws NoWithExpressionFoundException
     */
    public function getExpression(string $alias): WithExpression
    {
        /** @var WithExpression $expression */
        foreach ($this->getExpressions() as $expression) {
            if ($expression->getAlias() == $alias) {
                return $expression;
            }
        }
        throw new NoWithExpressionFoundException(sprintf("WithExpression with alias %s is not found", $alias));
    }
}
