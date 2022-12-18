<?php

namespace Se\Mdx\Structure;

class RangeExpression extends Expression
{
    private MemberExpression|SetExpression|RangeExpression|TupleExpression $minExpressions;
    private MemberExpression|SetExpression|RangeExpression|TupleExpression $maxExpressions;

    /**
     * @param MemberExpression|RangeExpression|SetExpression|TupleExpression $minExpressions
     * @param MemberExpression|RangeExpression|SetExpression|TupleExpression $maxExpressions
     */
    public function __construct(
        TupleExpression|SetExpression|RangeExpression|MemberExpression $minExpressions,
        TupleExpression|SetExpression|RangeExpression|MemberExpression $maxExpressions
    )
    {
        $this->minExpressions = $minExpressions;
        $this->maxExpressions = $maxExpressions;
    }

    public function __toString(): string
    {
        return sprintf("{%s:%s}", $this->minExpressions, $this->maxExpressions);
    }

    /**
     * @return MemberExpression|RangeExpression|SetExpression|TupleExpression
     */
    public function getMinExpressions(): TupleExpression|SetExpression|RangeExpression|MemberExpression
    {
        return $this->minExpressions;
    }

    /**
     * @return MemberExpression|RangeExpression|SetExpression|TupleExpression
     */
    public function getMaxExpressions(): TupleExpression|SetExpression|RangeExpression|MemberExpression
    {
        return $this->maxExpressions;
    }

}
