<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\BreakException;

class PipelineRunner extends PipelineBuilderFactory {

	/** @var PipelineBuilder[] */
	private array $pipelineBuilders = [];

	public function add(PipelineBuilder $builder, array $context = []): static {
		$this->pipelineBuilders[] = [$builder, $context];
		return $this;
	}

	public function exec(array $context = []) {
		/**
		 * @var PipelineBuilder $builder
		 */
		foreach ($this->pipelineBuilders as $pipelineBuilder) {
			[$builder, $builderContext] = $pipelineBuilder;
			$context = array_merge($context, $builderContext);
			try {
				return $builder->exec($context);
			} catch (BreakException $e) {
				// do nothing it just broke the current pipeline
			} catch (\Exception $e) {
				throw $e;
			}
		}
	}
}