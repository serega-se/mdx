<?php

namespace Se\Mdx;

use Se\Mdx\Exception\NoWithExpressionFoundException;
use Se\Mdx\Structure\CrossJoinExpression;
use Se\Mdx\Structure\MemberExpression;
use Se\Mdx\Structure\NoneEmptyExpression;
use Se\Mdx\Structure\Query;
use PHPUnit\Framework\TestCase;
use Se\Mdx\Structure\RangeExpression;
use Se\Mdx\Structure\SetExpression;
use Se\Mdx\Structure\TupleExpression;
use Se\Mdx\Structure\WithExpression;
use Se\Mdx\Structure\WithExpressionList;

class QueryTest extends TestCase
{
    public function testCreateMemberExpression()
    {
        $mdxQuery = (new Query())
            ->setColumns((new MemberExpression("[Measures].[Amount]")))
            ->setRows((new MemberExpression("[Product].[Product].[Name]")))
            ->setFrom("[Sales]");

        $this->assertEquals(
            $this->ntrim("SELECT 
                        [Measures].[Amount] ON COLUMNS, 
                        [Product].[Product].[Name] ON ROWS 
                    FROM [Sales]"),
            $this->ntrim($mdxQuery)
        );
    }

    public function testCreateTurpleExpression()
    {
        $mdxQuery = (new Query())
            ->setColumns(
                (new MemberExpression("[Measures].[Amount]"))
            )
            ->setRows(
                (new TupleExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Носок]")))
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Валенок]")))
            )->setFrom("[Sales]");

        $this->assertEquals(
            $this->ntrim("SELECT 
	                     [Measures].[Amount] ON COLUMNS, 
	                     ([Product].[Product].[Name].&[Носок], [Product].[Product].[Name].&[Валенок]) ON ROWS 
                         FROM [Sales]"),
            $this->ntrim($mdxQuery)
        );
    }

    public function testCreateSetExpression()
    {
        $mdxQuery = (new Query())
            ->setColumns(
                (new MemberExpression("[Measures].[Amount]"))
            )
            ->setRows(
                (new SetExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                    ->addExpression((new MemberExpression("[Date].[Year]")))
            )->setFrom("[Sales]");

        $this->assertEquals(
            $this->ntrim("SELECT
	                        [Measures].[Amount] ON COLUMNS,
	                        {[Product].[Product].[Name], [Date].[Year]} ON ROWS
                            FROM [Sales]"),
            $this->ntrim($mdxQuery)
        );
    }

    public function testCreateSubquery()
    {
        $mdxSubquery = (new Query())
            ->setColumns(
                (new MemberExpression("[Date].[Date].[Month].&[202101]"))
            )->setFrom("[Sales]");


        $mdxQuery = (new Query())
            ->setColumns(
                (new MemberExpression("[Measures].[Amount]"))
            )
            ->setRows(
                (new TupleExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Носок]")))
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Валенок]")))
            )->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim(
                "SELECT
	                [Measures].[Amount] ON COLUMNS,
	                ([Product].[Product].[Name].&[Носок], [Product].[Product].[Name].&[Валенок]) ON ROWS
                    FROM (
	                    SELECT
		                [Date].[Date].[Month].&[202101] ON COLUMNS
	                    FROM [Sales]
                    )"),
            $this->ntrim($mdxQuery)
        );
    }

    public function testCreateSubquery1()
    {

        $mdxSubquery1 = (new Query())
            ->setColumns(
                (new MemberExpression("[Product].[Category].[Id].&[10]"))
            )->setFrom("[Sales]");

        $mdxSubquery = (new Query())
            ->setColumns(
                (new MemberExpression("[Date].[Date].[Month].&[202101]"))
            )->setFrom($mdxSubquery1);

        $mdxQuery = (new Query())
            ->setColumns(
                (new MemberExpression("[Measures].[Amount]"))
            )
            ->setRows(
                (new TupleExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Носок]")))
                    ->addExpression((new MemberExpression("[Product].[Product].[Name].&[Валенок]")))
            )->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim(
                "SELECT
	                    [Measures].[Amount] ON COLUMNS,
	                    ([Product].[Product].[Name].&[Носок], [Product].[Product].[Name].&[Валенок]) ON ROWS
                    FROM (
	                    SELECT
		                    [Date].[Date].[Month].&[202101] ON COLUMNS
	                    FROM (
		                    SELECT
			                    [Product].[Category].[Id].&[10] ON COLUMNS
		                    FROM [Sales]
	                    )
                    )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateWithExpression()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
            (new WithExpression())
                ->setAlias('MySetName')
                ->setExpression((new SetExpression())
                    ->addExpression((new MemberExpression("[Measures].[Amount]")))
                    ->addExpression((new MemberExpression("[Measures].[Rest]")))
                )
        );


        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns($withExpressionList->getExpression('MySetName'))
            ->setRows((new MemberExpression("[Product].[Product].[Name]")))
            ->setFrom("[Sales]");

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            MySetName ON COLUMNS,
	                            [Product].[Product].[Name] ON ROWS
                            FROM [Sales]"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateRangeExpression()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns($withExpressionList->getExpression('MySetName'))
            ->setRows((new MemberExpression("[Product].[Product].[Name]")))
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            MySetName ON COLUMNS,
	                            [Product].[Product].[Name] ON ROWS
                            FROM (
	                            SELECT
		                        {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateNoneEmptyExpression()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns($withExpressionList->getExpression('MySetName'))
            ->setRows(
                (new NoneEmptyExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                    ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateNoneEmptyExpression1()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new SetExpression())
                    ->addExpression((new MemberExpression("[Address].[Town]")))
                    ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                    ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            {[Address].[Town], MySetName} ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateCrossJoinExpression()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new CrossJoinExpression())
                    ->addExpression((new MemberExpression("[Address].[Town]")))
                    ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                    ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            [Address].[Town] * MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateCrossJoinNoneEmptyExpression()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new CrossJoinExpression())
                ->addExpression(
                    (new NoneEmptyExpression())
                    ->addExpression((new MemberExpression("[Address].[Town]")))
                    ->addExpression($withExpressionList->getExpression('MySetName'))
                )
                ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            NoneEmpty([Address].[Town], MySetName) * MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateCrossJoinNoneEmptyExpression1()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new CrossJoinExpression())
                ->addExpression(
                    (new NoneEmptyExpression())
                    ->addExpression(
                        (new CrossJoinExpression())
                        ->addExpression((new MemberExpression("[Address].[Town]")))
                        ->addExpression(
                            (new NoneEmptyExpression())
                            ->addExpression((new MemberExpression("[Branch].[Name]")))
                            ->addExpression($withExpressionList->getExpression('MySetName'))
                        )
                    )->addExpression($withExpressionList->getExpression('MySetName'))
                )
                ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            NoneEmpty([Address].[Town] * NoneEmpty([Branch].[Name], MySetName), MySetName) * MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateCrossJoinNoneEmptyExpression2()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxSubquery = (new Query())
            ->setColumns(
                new RangeExpression(
                    (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                    (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                )
            )->setFrom("[Sales]");

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new CrossJoinExpression())
                ->addExpression(
                    (new NoneEmptyExpression())
                    ->addExpression(
                        (new CrossJoinExpression())
                        ->addExpression((new MemberExpression("[Address].[Town]")))
                        ->addExpression(
                            (new NoneEmptyExpression())
                            ->addExpression(
                                (new CrossJoinExpression())
                                ->addExpression((new MemberExpression("[Branch].[Name]")))
                                ->addExpression(
                                    (new NoneEmptyExpression())
                                    ->addExpression((new MemberExpression("[SaleType].[Name]")))
                                    ->addExpression($withExpressionList->getExpression('MySetName'))
                                )
                            )
                            ->addExpression($withExpressionList->getExpression('MySetName'))
                        )
                    )
                    ->addExpression($withExpressionList->getExpression('MySetName'))
                )
                ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                    ->addExpression((new MemberExpression("[Product].[Product].[Name]")))
                    ->addExpression($withExpressionList->getExpression('MySetName'))
            )
            ->setFrom($mdxSubquery);

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            NoneEmpty([Address].[Town] * NoneEmpty([Branch].[Name] * NoneEmpty([SaleType].[Name], MySetName),
			                        MySetName), MySetName) * MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name], MySetName) ON ROWS
                            FROM (
	                            SELECT
		                            {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
	                            FROM [Sales]
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    /**
     * @throws NoWithExpressionFoundException
     */
    public function testCreateCrossJoinNoneEmptyExpression3()
    {
        $withExpressionList = (new WithExpressionList())
            ->addExpression(
                (new WithExpression())
                    ->setAlias('MySetName')
                    ->setExpression((new SetExpression())
                        ->addExpression((new MemberExpression("[Measures].[Amount]")))
                        ->addExpression((new MemberExpression("[Measures].[Rest]")))
                    )
            );

        $mdxQuery = (new Query())
            ->setWith($withExpressionList)
            ->setColumns(
                (new CrossJoinExpression())
                ->addExpression(
                    (new NoneEmptyExpression())
                    ->addExpression(
                        (new CrossJoinExpression())
                        ->addExpression((new MemberExpression("[Address].[Town]")))
                        ->addExpression(
                            (new NoneEmptyExpression())
                            ->addExpression(
                                (new CrossJoinExpression())
                                ->addExpression((new MemberExpression("[Branch].[Name]")))
                                 ->addExpression(
                                     (new NoneEmptyExpression())
                                     ->addExpression((new MemberExpression("[SaleType].[Name]")))
                                     ->addExpression($withExpressionList->getExpression('MySetName'))
                                 )
                            )
                            ->addExpression($withExpressionList->getExpression('MySetName'))
                        )
                    )->addExpression(
                        $withExpressionList->getExpression('MySetName')
                    )
                )
                ->addExpression($withExpressionList->getExpression('MySetName')))
            ->setRows(
                (new NoneEmptyExpression())
                ->addExpression(
                    (new CrossJoinExpression())
                    ->addExpression(new MemberExpression("[Product].[Product].[Name]"))
                    ->addExpression(
                            (new NoneEmptyExpression())
                            ->addExpression(new MemberExpression("[Date].[Date].[Day]"))
                            ->addExpression($withExpressionList->getExpression('MySetName'))
                    )
                )
                ->addExpression($withExpressionList->getExpression('MySetName'))
                )
            ->setFrom(
                (new Query())
                ->setColumns(
                    (new CrossJoinExpression())
                    ->addExpression(
                        (new SetExpression())
                        ->addExpression(new MemberExpression("[Product].[Product].&[134947954]"))
                        ->addExpression(new MemberExpression("[Product].[Product].&[134947981]"))
                        ->addExpression(new MemberExpression("[Product].[Product].&[11145970]"))
                        ->addExpression(new MemberExpression("[Product].[Product].&[101362503]"))
                    )
                    ->addExpression(
                        (new SetExpression())
                        ->addExpression(new MemberExpression("[Address].[Region].&[77]"))
                        ->addExpression(new MemberExpression("[Address].[Region].&[54]"))
                    )
                    ->addExpression(
                        (new SetExpression())
                        ->addExpression(new MemberExpression("[Branch].[Name].&[6332]"))
                        ->addExpression(new MemberExpression("[Branch].[Name].&[295]"))
                    )
                )
                ->setFrom(
                    (new Query())
                    ->setColumns(
                        new RangeExpression(
                            (new MemberExpression("[Date].[Date].[Month].&[202101]")),
                            (new MemberExpression("[Date].[Date].[Month].&[202112]"))
                        )
                    )
                ->setFrom("[Sales]")
                )
            );

        $this->assertEquals(
            $this->ntrim("WITH SET MySetName AS {[Measures].[Amount], [Measures].[Rest]}
                            SELECT
	                            NoneEmpty([Address].[Town] * NoneEmpty([Branch].[Name] * NoneEmpty([SaleType].[Name], MySetName),
			                                MySetName), MySetName) * MySetName ON COLUMNS,
	                            NoneEmpty([Product].[Product].[Name] * NoneEmpty([Date].[Date].[Day], MySetName), MySetName) ON ROWS
                            FROM (
	                            SELECT 
		                        {[Product].[Product].&[134947954], [Product].[Product].&[134947981], [Product].[Product].&[11145970], [Product].[Product].&[101362503]} * {[Address].[Region].&[77], [Address].[Region].&[54]} * {[Branch].[Name].&[6332], [Branch].[Name].&[295]} ON COLUMNS
	                            FROM (
		                            SELECT
			                        {[Date].[Date].[Month].&[202101]:[Date].[Date].[Month].&[202112]} ON COLUMNS
		                            FROM [Sales]
	                            )
                            )"),
            $this->ntrim($mdxQuery)
        );
    }

    public function ntrim($s)
    {
        $s =  str_replace(array("\t", "\n"), ' ', $s);
        $s =  str_replace(array("\r", "\n"), '', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }
}
