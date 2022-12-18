<?php

namespace Se\Mdx\Structure;

class NullExpression  extends Expression
{
    public function __toString(): string
    {
        return "";
    }
}
